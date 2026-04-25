<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ServerStatsController extends Controller
{
    /**
     * Retorna estadísticas del servidor en JSON.
     * Compatible con Windows (wmic) y Linux (/proc).
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'cpu'    => $this->getCpuUsage(),
            'ram'    => $this->getRamInfo(),
            'disk'   => $this->getDiskInfo(),
            'php'    => $this->getPhpInfo(),
            'uptime' => $this->getUptime(),
            'ts'     => now()->format('H:i:s'),
        ]);
    }

    // ── CPU ──────────────────────────────────────────────────────────

    private function getCpuUsage(): array
    {
        $pct = null;

        if (PHP_OS_FAMILY === 'Windows') {
            $out = shell_exec('wmic cpu get LoadPercentage /value 2>nul');
            if ($out && preg_match('/LoadPercentage=(\d+)/i', $out, $m)) {
                $pct = (int) $m[1];
            }
        } else {
            // Linux: dos lecturas de /proc/stat separadas 200ms
            $stat1 = $this->readProcStat();
            usleep(200000);
            $stat2 = $this->readProcStat();

            if ($stat1 && $stat2) {
                $idle1  = $stat1[3] + $stat1[4];
                $total1 = array_sum($stat1);
                $idle2  = $stat2[3] + $stat2[4];
                $total2 = array_sum($stat2);

                $deltaTotal = $total2 - $total1;
                $deltaIdle  = $idle2 - $idle1;

                $pct = $deltaTotal > 0 ? (int) round((1 - $deltaIdle / $deltaTotal) * 100) : 0;
            }
        }

        // Fallback: carga de PHP en el proceso actual
        if ($pct === null) {
            $pct = -1; // Indica "no disponible"
        }

        return [
            'pct'   => max(0, min(100, $pct ?? 0)),
            'avail' => $pct !== -1,
            'cores' => $this->getCoreCount(),
        ];
    }

    private function readProcStat(): ?array
    {
        if (!file_exists('/proc/stat')) return null;
        $line = fgets(fopen('/proc/stat', 'r'));
        if (!$line) return null;
        $parts = preg_split('/\s+/', trim($line));
        array_shift($parts); // quitar "cpu"
        return array_map('intval', $parts);
    }

    private function getCoreCount(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $out = shell_exec('wmic cpu get NumberOfCores /value 2>nul');
            if ($out && preg_match('/NumberOfCores=(\d+)/i', $out, $m)) {
                return (int) $m[1];
            }
        } elseif (file_exists('/proc/cpuinfo')) {
            $out = shell_exec('nproc 2>/dev/null');
            if ($out) return (int) trim($out);
        }
        return 1;
    }

    // ── RAM ──────────────────────────────────────────────────────────

    private function getRamInfo(): array
    {
        $total = $used = $free = null;

        if (PHP_OS_FAMILY === 'Windows') {
            $tot = shell_exec('wmic ComputerSystem get TotalPhysicalMemory /value 2>nul');
            $fre = shell_exec('wmic OS get FreePhysicalMemory /value 2>nul');

            if ($tot && preg_match('/TotalPhysicalMemory=(\d+)/i', $tot, $m)) {
                $total = (int) $m[1]; // bytes
            }
            if ($fre && preg_match('/FreePhysicalMemory=(\d+)/i', $fre, $m)) {
                $free = (int) $m[1] * 1024; // KB → bytes
            }
            if ($total && $free !== null) {
                $used = $total - $free;
            }
        } elseif (file_exists('/proc/meminfo')) {
            $info = [];
            foreach (file('/proc/meminfo') as $line) {
                [$key, $val] = explode(':', $line);
                $info[trim($key)] = (int) trim($val) * 1024; // kB → bytes
            }
            $total = $info['MemTotal'] ?? null;
            $free  = ($info['MemAvailable'] ?? $info['MemFree'] ?? null);
            $used  = $total && $free !== null ? $total - $free : null;
        }

        $pct = ($total && $used !== null && $total > 0)
            ? (int) round($used / $total * 100)
            : -1;

        return [
            'total_gb' => $total ? round($total / (1024 ** 3), 1) : null,
            'used_gb'  => $used  ? round($used  / (1024 ** 3), 1) : null,
            'free_gb'  => $free  ? round($free  / (1024 ** 3), 1) : null,
            'pct'      => max(0, min(100, $pct >= 0 ? $pct : 0)),
            'avail'    => $pct !== -1,
        ];
    }

    // ── DISCO ─────────────────────────────────────────────────────────

    private function getDiskInfo(): array
    {
        $path = PHP_OS_FAMILY === 'Windows' ? 'C:\\' : '/';
        $total = @disk_total_space($path);
        $free  = @disk_free_space($path);

        if ($total && $free !== false) {
            $used = $total - $free;
            return [
                'total_gb' => round($total / (1024 ** 3), 1),
                'used_gb'  => round($used  / (1024 ** 3), 1),
                'free_gb'  => round($free  / (1024 ** 3), 1),
                'pct'      => (int) round($used / $total * 100),
                'avail'    => true,
            ];
        }

        return ['avail' => false, 'pct' => 0];
    }

    // ── PHP / Proceso ─────────────────────────────────────────────────

    private function getPhpInfo(): array
    {
        return [
            'version'     => PHP_VERSION,
            'memory_used' => round(memory_get_usage(true) / (1024 ** 2), 1),    // MB
            'memory_peak' => round(memory_get_peak_usage(true) / (1024 ** 2), 1),
            'memory_limit'=> ini_get('memory_limit'),
            'os'          => php_uname('s') . ' ' . php_uname('r'),
        ];
    }

    // ── UPTIME ────────────────────────────────────────────────────────

    private function getUptime(): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $out = shell_exec('net statistics server 2>nul | findstr "Statistics since"');
            if ($out) return trim(str_replace('Statistics since', '', $out));
            return null;
        }

        if (file_exists('/proc/uptime')) {
            $seconds = (int) file_get_contents('/proc/uptime');
            $d = intdiv($seconds, 86400);
            $h = intdiv($seconds % 86400, 3600);
            $m = intdiv($seconds % 3600, 60);
            return ($d > 0 ? "{$d}d " : '') . "{$h}h {$m}m";
        }

        return null;
    }
}

<script>
function toggleModulo(mod) {
    const block = document.getElementById('block-' + mod);
    const icon  = document.getElementById('icon-' + mod);
    block.classList.toggle('show');
    icon.classList.toggle('open');
}

function toggleModuloCheck(checkbox) {
    const mod   = checkbox.dataset.mod;
    const checks = document.querySelectorAll('.mod-' + mod + ' input[type=checkbox]');
    checks.forEach(c => c.checked = checkbox.checked);
}

function toggleAll(state) {
    document.querySelectorAll('input[name="permisos[]"]').forEach(c => c.checked = state);
    document.querySelectorAll('.mod-all').forEach(c => c.checked = state);
}

// Actualizar checkboxes "mod-all" cuando se marcan individualmente
document.addEventListener('change', function(e) {
    if (e.target.name === 'permisos[]') {
        const perm = e.target.closest('.perm-check');
        if (!perm) return;
        const classes = [...perm.classList];
        const modClass = classes.find(c => c.startsWith('mod-') && c !== 'mod-all');
        if (!modClass) return;
        const mod = modClass.replace('mod-', '');
        const allChecks = document.querySelectorAll('.' + modClass + ' input[type=checkbox]');
        const allAll    = [...allChecks].every(c => c.checked);
        const modAllCb  = document.querySelector('.mod-all[data-mod="' + mod + '"]');
        if (modAllCb) modAllCb.checked = allAll;
    }
});
</script>

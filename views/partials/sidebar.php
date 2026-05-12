<?php
/**
 * Barra lateral de navegacio adaptada al rol de l'usuari autenticat.
 *
 * @var array|null $currentUser Usuari de sessio; si no existeix no es mostra res.
 */
if (!$currentUser) {
    return;
}
$rol = $currentUser['rol'] ?? '';
?>
<?php if ($rol === ROLE_PROFESSOR): ?>
<aside class="rail rail--prof" aria-label="Navegació professorat">
    <div class="rail__intro">
        <a class="rail__home" href="<?= h(url('professorat/index.php')) ?>" style="
            background: none;
            border: none;
            padding: 0.5rem 0.75rem;
        ">
            <span class="rail__badge" aria-hidden="true" style="font-size: 0.7rem; color: var(--mist);">PROF</span>
            <span class="rail__label" style="font-size: 0.88rem; font-weight: 700; color: var(--snow);">Panell</span>
        </a>
    </div>
    <nav class="rail-nav" style="gap: 0.1rem;">
        <span class="rail-nav__title">Menú</span>
        <a class="rail-link <?= h(nav_active('professorat/index.php')) ?>" href="<?= h(url('professorat/index.php')) ?>"><span class="rail-link__glow"></span><span class="rail-link__t">Inici</span></a>
        <a class="rail-link <?= h(nav_active('professorat/dispositius_aula.php')) ?>" href="<?= h(url('professorat/dispositius_aula.php')) ?>"><span class="rail-link__glow"></span><span class="rail-link__t">Aula</span></a>
        <a class="rail-link <?= h(nav_active('professorat/assignacions.php')) ?>" href="<?= h(url('professorat/assignacions.php')) ?>"><span class="rail-link__glow"></span><span class="rail-link__t">Assignacions</span></a>
        <a class="rail-link <?= h(nav_active('professorat/alumnes.php')) ?>" href="<?= h(url('professorat/alumnes.php')) ?>"><span class="rail-link__glow"></span><span class="rail-link__t">Alumnes</span></a>
        <a class="rail-link <?= h(nav_active('professorat/material.php')) ?>" href="<?= h(url('professorat/material.php')) ?>"><span class="rail-link__glow"></span><span class="rail-link__t">Material</span></a>
        <a class="rail-link <?= h(nav_active('professorat/incidencies.php')) ?>" href="<?= h(url('professorat/incidencies.php')) ?>"><span class="rail-link__glow"></span><span class="rail-link__t">Incidències</span></a>
        <a class="rail-link <?= h(nav_active('professorat/usuaris.php')) ?>" href="<?= h(url('professorat/usuaris.php')) ?>"><span class="rail-link__glow"></span><span class="rail-link__t">Usuaris</span></a>
    </nav>
    <p class="rail__foot" style="font-size: 0.58rem; color: rgba(196,160,190,0.3);">Institut Montsia</p>
</aside>
<?php elseif ($rol === ROLE_STUDENT): ?>
<aside class="rail rail--alum" aria-label="Navegació alumnat">
    <div class="rail__intro">
        <a class="rail__home" href="<?= h(url('alumnat/index.php')) ?>" style="
            background: none;
            border: none;
            padding: 0.5rem 0.75rem;
        ">
            <span class="rail__badge" aria-hidden="true" style="font-size: 0.7rem; color: var(--mist);">ALUM</span>
            <span class="rail__label" style="font-size: 0.88rem; font-weight: 700; color: var(--snow);">El meu espai</span>
        </a>
    </div>
    <nav class="rail-nav" style="gap: 0.1rem;">
        <span class="rail-nav__title">Alumnat</span>
        <a class="rail-link <?= h(nav_active('alumnat/index.php')) ?>" href="<?= h(url('alumnat/index.php')) ?>"><span class="rail-link__glow"></span><span class="rail-link__t">Dispositius</span></a>
    </nav>
    <p class="rail__foot" style="font-size: 0.58rem; color: rgba(196,160,190,0.3);">Institut Montsia</p>
</aside>
<?php endif; ?>

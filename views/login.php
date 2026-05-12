<?php
/**
 * Vista del formulari public d'inici de sessio.
 *
 * Rep les dades preparades per login.php i mostra qualsevol error de validacio
 * sense executar logica d'autenticacio dins la plantilla.
 *
 * @var string $error Missatge d'error visible, buit si no n'hi ha cap.
 * @var string $correu Correu escrit previament per mantenir el formulari.
 */
?>
<style>
.gate__orbit {
    display: flex;
    align-items: center;
    justify-content: center;
    width: min(340px, 80vw);
    aspect-ratio: 1;
    margin: 0 auto 0;
    position: relative;
}

.hex-wrap {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hex-svg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: visible;
}

.hex-outer {
    animation: hexspin 28s linear infinite;
    transform-origin: center;
    transform-box: fill-box;
}

.hex-inner {
    animation: hexspin 18s linear infinite reverse;
    transform-origin: center;
    transform-box: fill-box;
}

.hex-mid {
    animation: hexspin 38s linear infinite;
    transform-origin: center;
    transform-box: fill-box;
}

@keyframes hexspin {
    to { transform: rotate(360deg); }
}

.hex-core {
    position: relative;
    z-index: 2;
    width: 42%;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
    background: var(--glass);
    border: 1px solid var(--glass-b);
    box-shadow: 0 0 60px rgba(247, 224, 0, 0.22);
}

.hex-core img {
    width: 55%;
    height: auto;
    object-fit: contain;
}

.gate__ring,
.gate__ring--outer,
.gate__ring--inner {
    display: none;
}
</style>

<section class="gate" style="
    grid-template-columns: 1fr;
    grid-template-rows: auto auto;
    min-height: calc(100vh - 6rem);
    padding: 2rem clamp(1rem, 5vw, 4rem) 3rem;
    align-items: start;
    gap: 0;
">
    <div class="gate__stage" style="
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 2rem;
        margin-bottom: 3rem;
        padding: 0;
    ">
        <div>
            <ul class="gate__tags" style="margin-bottom: 1.25rem;">
                <li>Professorat</li>
                <li>Alumnat</li>
                <li>Actualitzat en temps real</li>
            </ul>
            <h2 class="gate__headline" style="max-width: none; font-size: clamp(2.2rem, 5vw, 3.8rem);">Un sol espai per al material</h2>
            <p class="gate__sub" style="max-width: none; font-size: 1.1rem;">Assignacions, aules i incidències amb lectura ràpida de l'estat.</p>
        </div>

        <div class="gate__orbit">
            <div class="hex-wrap">
                <svg class="hex-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <g class="hex-outer">
                        <polygon points="100,4 188,52 188,148 100,196 12,148 12,52"
                            fill="none" stroke="rgba(247,224,0,0.28)" stroke-width="1.5" stroke-dasharray="8 5"/>
                    </g>
                    <g class="hex-mid">
                        <polygon points="100,18 174,60 174,140 100,182 26,140 26,60"
                            fill="none" stroke="rgba(255,45,120,0.22)" stroke-width="1" stroke-dasharray="4 8"/>
                    </g>
                    <g class="hex-inner">
                        <polygon points="100,32 162,68 162,132 100,168 38,132 38,68"
                            fill="none" stroke="rgba(247,224,0,0.45)" stroke-width="2"/>
                    </g>
                </svg>
                <div class="hex-core">
                    <img src="<?= h(asset_url('img/montsia-removebg-preview.png')) ?>" width="88" height="88" alt="Institut Montsia">
                </div>
            </div>
        </div>
    </div>

    <div class="gate__access" style="justify-content: flex-start; width: 100%;">
        <div class="glass-card" style="max-width: 100%; width: 100%; padding: 2rem 2rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem;">
                <p style="margin: 0; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--mist);">Inici de sessió &mdash; Institut Montsia</p>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error" style="margin: 0; padding: 0.4rem 0.75rem; font-size: 0.8rem;"><?= h($error) ?></div>
                <?php endif; ?>
            </div>

            <div style="height: 1px; background: var(--glass-b); margin-bottom: 1.5rem;"></div>

            <form method="post" action="<?= h(url('login.php')) ?>" class="glass-form" style="
                display: grid;
                grid-template-columns: 1fr 1fr auto;
                align-items: end;
                gap: 1rem;
            ">
                <?= csrf_field() ?>
                <label class="glass-field" style="margin: 0;">
                    <span>Correu</span>
                    <input id="correu" name="correu" type="email" value="<?= h($correu ?? '') ?>" autocomplete="email" required placeholder="nom.cognoms@institutmontsia.org">
                </label>
                <label class="glass-field" style="margin: 0;">
                    <span>Contrasenya</span>
                    <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="········">
                </label>
                <button class="btn btn-magic" type="submit" style="height: 46px; padding: 0 2rem; white-space: nowrap;">
                    Continuar
                </button>
            </form>
        </div>
    </div>
</section>

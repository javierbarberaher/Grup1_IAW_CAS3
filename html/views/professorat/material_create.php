<?php
/**
 * Formulari d'alta de material i, si cal, del seu tipus associat.
 *
 * @var array $tipusMaterial Tipus existents que es poden reutilitzar.
 * @var array $ubicacions Ubicacions disponibles per al nou dispositiu.
 */
?>
<section class="page-heading" style="background:none;backdrop-filter:none;border:none;box-shadow:none;padding:0.25rem 0 1.5rem;">
    <div>
        <h1 style="font-size:clamp(1.1rem,2vw,1.5rem);background:none;color:var(--snow);-webkit-text-fill-color:var(--snow);font-weight:700;margin:0;">Nou material</h1>
        <p>Alta de dispositius i maquinari a l'inventari.</p>
    </div>
    <a class="btn btn-secondary" href="<?= h(url('professorat/material.php')) ?>">Tornar a material</a>
</section>

<form method="post" class="grid two">
    <?= csrf_field() ?>
    <section class="card">
        <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Tipus de material</h2>
        <div class="field">
            <label for="idTipus">Tipus existent</label>
            <select id="idTipus" name="idTipus">
                <option value="0">Crear tipus nou</option>
                <?php foreach ($tipusMaterial as $tipus): ?>
                    <option value="<?= (int) $tipus['id'] ?>">
                        <?= h($tipus['tipus'] . ' - ' . $tipus['model'] . ' (' . $tipus['origen'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="tipus">Nou tipus</label>
                <input id="tipus" name="tipus" placeholder="Portatil, monitor...">
            </div>
            <div class="field">
                <label for="model">Nou model</label>
                <input id="model" name="model" placeholder="Model comercial">
            </div>
            <div class="field">
                <label for="origen">Origen</label>
                <input id="origen" name="origen" placeholder="Centre, Departament...">
            </div>
        </div>
    </section>

    <section class="card">
        <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Dades del dispositiu</h2>
        <div class="form-grid">
            <div class="field">
                <label for="idInventari">Inventari</label>
                <input id="idInventari" name="idInventari" maxlength="10" required>
            </div>
            <div class="field">
                <label for="idUbicacio">Ubicacio</label>
                <select id="idUbicacio" name="idUbicacio" required>
                    <option value="">Selecciona ubicacio</option>
                    <?php foreach ($ubicacions as $ubicacio): ?>
                        <option value="<?= (int) $ubicacio['id'] ?>"><?= h($ubicacio['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="etiquetaDepInf">Etiqueta Dep. Inf.</label>
                <input id="etiquetaDepInf" name="etiquetaDepInf">
            </div>
            <div class="field">
                <label for="numSerie">Numero de serie</label>
                <input id="numSerie" name="numSerie">
            </div>
            <div class="field">
                <label for="macEthernet">MAC Ethernet</label>
                <input id="macEthernet" name="macEthernet">
            </div>
            <div class="field">
                <label for="macWifi">MAC WiFi</label>
                <input id="macWifi" name="macWifi">
            </div>
            <div class="field">
                <label for="SACE">SACE</label>
                <input id="SACE" name="SACE">
            </div>
            <div class="field">
                <label for="dataAdquisicio">Data adquisicio</label>
                <input id="dataAdquisicio" name="dataAdquisicio" type="date">
            </div>
        </div>
    </section>

    <section class="card" style="grid-column: 1 / -1;">
        <button class="btn btn-primary" type="submit">Crear material</button>
    </section>
</form>

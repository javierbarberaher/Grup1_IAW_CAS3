<?php
/**
 * Inventari de material amb filtres i formulari d'edicio en linia.
 *
 * @var array $materials Material mostrat a la taula.
 * @var array $tipusMaterial Tipus i models disponibles.
 * @var array $ubicacions Ubicacions registrades.
 * @var array $filters Valors aplicats als filtres.
 * @var array|null $editMaterial Material carregat per editar o null.
 */
?>
<section class="page-heading" style="background:none;backdrop-filter:none;border:none;box-shadow:none;padding:0.25rem 0 1.5rem;">
    <div>
        <h1 style="font-size:clamp(1.1rem,2vw,1.5rem);background:none;color:var(--snow);-webkit-text-fill-color:var(--snow);font-weight:700;margin:0;">Material</h1>
        <p>Inventari de dispositius i maquinari del centre.</p>
    </div>
    <a class="btn btn-primary" href="<?= h(url('professorat/material_create.php')) ?>">Nou material</a>
</section>

<section class="card">
    <form method="get" class="form-grid">
        <div class="field">
            <label for="tipus">Tipus</label>
            <select id="tipus" name="tipus">
                <option value="0">Tots</option>
                <?php foreach ($tipusMaterial as $tipus): ?>
                    <option value="<?= (int) $tipus['id'] ?>" <?= $filters['tipus'] === (int) $tipus['id'] ? 'selected' : '' ?>>
                        <?= h($tipus['tipus'] . ' - ' . $tipus['model']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="ubicacio">Ubicacio</label>
            <select id="ubicacio" name="ubicacio">
                <option value="0">Totes</option>
                <?php foreach ($ubicacions as $ubicacio): ?>
                    <option value="<?= (int) $ubicacio['id'] ?>" <?= $filters['ubicacio'] === (int) $ubicacio['id'] ? 'selected' : '' ?>>
                        <?= h($ubicacio['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="q">Cerca</label>
            <input id="q" name="q" value="<?= h($filters['q']) ?>" placeholder="Inventari, serie, model...">
        </div>
        <button class="btn btn-primary" type="submit">Filtrar</button>
        <a class="btn btn-secondary" href="<?= h(url('professorat/material.php')) ?>">Netejar</a>
    </form>
</section>

<?php if ($editMaterial): ?>
<section class="card" style="margin-top: 1rem;">
    <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Editar material</h2>
    <form method="post" class="form-grid wide">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= (int) $editMaterial['id'] ?>">
        <div class="field">
            <label for="edit_idTipus">Tipus</label>
            <select id="edit_idTipus" name="idTipus" required>
                <?php foreach ($tipusMaterial as $tipus): ?>
                    <option value="<?= (int) $tipus['id'] ?>" <?= (int) $editMaterial['idTipus'] === (int) $tipus['id'] ? 'selected' : '' ?>>
                        <?= h($tipus['tipus'] . ' - ' . $tipus['model']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="edit_idInventari">Inventari</label>
            <input id="edit_idInventari" name="idInventari" maxlength="10" value="<?= h($editMaterial['idInventari']) ?>" required>
        </div>
        <div class="field">
            <label for="edit_idUbicacio">Ubicacio</label>
            <select id="edit_idUbicacio" name="idUbicacio" required>
                <?php foreach ($ubicacions as $ubicacio): ?>
                    <option value="<?= (int) $ubicacio['id'] ?>" <?= (int) $editMaterial['idUbicacio'] === (int) $ubicacio['id'] ? 'selected' : '' ?>>
                        <?= h($ubicacio['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="edit_etiquetaDepInf">Etiqueta Dep. Inf.</label>
            <input id="edit_etiquetaDepInf" name="etiquetaDepInf" value="<?= h($editMaterial['etiquetaDepInf'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="edit_numSerie">Numero de serie</label>
            <input id="edit_numSerie" name="numSerie" value="<?= h($editMaterial['numSerie'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="edit_macEthernet">MAC Ethernet</label>
            <input id="edit_macEthernet" name="macEthernet" value="<?= h($editMaterial['macEthernet'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="edit_macWifi">MAC WiFi</label>
            <input id="edit_macWifi" name="macWifi" value="<?= h($editMaterial['macWifi'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="edit_SACE">SACE</label>
            <input id="edit_SACE" name="SACE" value="<?= h($editMaterial['SACE'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="edit_dataAdquisicio">Data adquisicio</label>
            <input id="edit_dataAdquisicio" name="dataAdquisicio" type="date" value="<?= h($editMaterial['dataAdquisicio'] ?? '') ?>">
        </div>
        <div class="actions">
            <button class="btn btn-primary" type="submit">Guardar canvis</button>
            <a class="btn btn-secondary" href="<?= h(url('professorat/material.php')) ?>">Cancel.lar</a>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="panel" style="margin-top: 1rem;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Tipus / model</th>
                    <th>Ubicacio</th>
                    <th>Assignacio</th>
                    <th>Incidencia</th>
                    <th>Dades</th>
                    <th>Accions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($materials as $material): ?>
                <tr>
                    <td>
                        <strong><?= h(material_label($material)) ?></strong>
                        <div class="muted"><?= display_value($material['etiquetaDepInf']) ?></div>
                    </td>
                    <td>
                        <?= h($material['tipus']) ?>
                        <div class="muted"><?= h($material['model']) ?></div>
                    </td>
                    <td><?= h($material['ubicacio']) ?></td>
                    <td>
                        <?php if ($material['idAlumne']): ?>
                            <a href="<?= h(url('professorat/alumne_detall.php?id=' . (int) $material['idAlumne'])) ?>"><?= h($material['alumne']) ?></a>
                        <?php else: ?>
                            <span class="status-badge status-warning">Disponible</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($material['idIncidencia']): ?>
                            <span class="status-badge <?= h(status_class($material['estatIncidencia'])) ?>"><?= h($material['estatIncidencia']) ?></span>
                        <?php else: ?>
                            <span class="status-badge status-ok">Sense incidencia</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div>Serie: <?= display_value($material['numSerie']) ?></div>
                        <div>Compra: <?= display_date($material['dataAdquisicio']) ?></div>
                    </td>
                    <td class="actions">
                        <a class="btn btn-small btn-primary" href="<?= h(url('professorat/material.php?edit=' . (int) $material['id'])) ?>">Editar</a>
                        <form method="post" data-confirm="Vols eliminar aquest material?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $material['id'] ?>">
                            <button class="btn btn-small btn-danger" type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($materials)): ?>
                <tr><td colspan="7">No s'ha trobat material.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

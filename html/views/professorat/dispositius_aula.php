<?php
/**
 * Vista de dispositius agrupats per aula o ubicacio.
 *
 * @var array $rooms Aules disponibles per navegar.
 * @var array $devicesByRoom Material agrupat pel nom de l'aula.
 */
?>
<section class="page-heading" style="background:none;backdrop-filter:none;border:none;box-shadow:none;padding:0.25rem 0 1.5rem;">
    <div>
        <h1 style="font-size:clamp(1.1rem,2vw,1.5rem);background:none;color:var(--snow);-webkit-text-fill-color:var(--snow);font-weight:700;margin:0;">Dispositius per aula</h1>
        <p>Consulta el material agrupat per ubicacio i tipus.</p>
    </div>
</section>

<section class="card">
    <form class="form-grid" method="get">
        <div class="field">
            <label for="ubicacio">Aula</label>
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
        <button class="btn btn-primary" type="submit">Filtrar</button>
        <a class="btn btn-secondary" href="<?= h(url('professorat/dispositius_aula.php')) ?>">Netejar</a>
    </form>
</section>

<section class="grid two" style="margin-top: 1rem;">
    <article class="panel">
        <div class="card">
            <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Recompte total per tipus</h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tipus</th>
                        <th>Model</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($totalsByType as $row): ?>
                    <tr>
                        <td><?= h($row['tipus']) ?></td>
                        <td><?= h($row['model']) ?></td>
                        <td><?= (int) $row['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($totalsByType)): ?>
                    <tr><td colspan="3">No hi ha resultats amb aquests filtres.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card">
        <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Lectura rapida</h2>
        <p class="muted">Els filtres afecten tant el recompte total com el detall per aula. Les assignacions es consulten a la pantalla d'assignacions.</p>
        <a class="btn btn-secondary" href="<?= h(url('professorat/assignacions.php')) ?>">Veure assignacions</a>
    </article>
</section>

<?php foreach ($byClassroom as $aula => $materials): ?>
    <section class="panel" style="margin-top: 1rem;">
        <div class="card">
            <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Aula <?= h($aula) ?></h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tipus</th>
                        <th>Model</th>
                        <th>Total unitats</th>
                    </tr>
                </thead>
                <tbody>
                <?php $classroomTotal = 0; ?>
                <?php foreach ($materials as $material): ?>
                    <?php $classroomTotal += (int) $material['total']; ?>
                    <tr>
                        <td><?= h($material['tipus']) ?></td>
                        <td><?= h($material['model']) ?></td>
                        <td><?= (int) $material['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Total aula</td>
                        <td><?= (int) $classroomTotal ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
<?php endforeach; ?>

<?php if (empty($byClassroom)): ?>
    <section class="empty-state" style="margin-top: 1rem;">
        <h1 style="font-size:clamp(1.1rem,2vw,1.5rem);background:none;color:var(--snow);-webkit-text-fill-color:var(--snow);font-weight:700;margin:0;">No hi ha dispositius</h1>
        <p>No s'ha trobat material per als filtres seleccionats.</p>
    </section>
<?php endif; ?>

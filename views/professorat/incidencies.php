<?php
/**
 * Gestio d'incidencies: creacio, filtratge i canvi d'estat.
 *
 * @var array $students Alumnes disponibles per associar una incidencia.
 * @var array $materials Material que pot aparèixer en una incidencia.
 * @var array $states Estats possibles d'una incidencia.
 * @var array $incidents Incidencies que compleixen el filtre actual.
 * @var string $filter Filtre seleccionat, per exemple obertes o totes.
 */
?>
<section class="page-heading" style="background:none;backdrop-filter:none;border:none;box-shadow:none;padding:0.25rem 0 1.5rem;">
    <div>
        <h1 style="font-size:clamp(1.1rem,2vw,1.5rem);background:none;color:var(--snow);-webkit-text-fill-color:var(--snow);font-weight:700;margin:0;">Incidencies</h1>
        <p>Seguiment d'incidencies obertes i historics de dispositius.</p>
    </div>
    <div class="actions">
        <a class="btn <?= $filter === 'obertes' ? 'btn-primary' : 'btn-secondary' ?>" href="<?= h(url('professorat/incidencies.php?filter=obertes')) ?>">Obertes</a>
        <a class="btn <?= $filter === 'totes' ? 'btn-primary' : 'btn-secondary' ?>" href="<?= h(url('professorat/incidencies.php?filter=totes')) ?>">Totes</a>
    </div>
</section>

<section class="card">
    <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Nova incidencia</h2>
    <form method="post" class="form-grid wide">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="field">
            <label for="idAlumne">Alumne</label>
            <select id="idAlumne" name="idAlumne" required>
                <option value="">Selecciona alumne</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= (int) $student['id'] ?>"><?= h(student_full_name($student) . ' (' . $student['grupClasse'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="idDispositiu">Dispositiu</label>
            <select id="idDispositiu" name="idDispositiu" required>
                <option value="">Selecciona material</option>
                <?php foreach ($materials as $material): ?>
                    <option value="<?= (int) $material['id'] ?>"><?= h($material['tipus'] . ' - ' . $material['model'] . ' - ' . material_label($material)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="idEstat">Estat</label>
            <select id="idEstat" name="idEstat" required>
                <option value="">Selecciona estat</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?= (int) $state['id'] ?>"><?= h($state['estat']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="dataOberta">Data oberta</label>
            <input id="dataOberta" name="dataOberta" type="date" value="<?= h(date('Y-m-d')) ?>">
        </div>
        <div class="field">
            <label for="dataTancada">Data tancada</label>
            <input id="dataTancada" name="dataTancada" type="date">
        </div>
        <div class="field" style="grid-column: 1 / -1;">
            <label for="informacio">Informacio</label>
            <textarea id="informacio" name="informacio" required></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Crear incidencia</button>
    </form>
</section>

<section class="panel" style="margin-top: 1rem;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Material</th>
                    <th>Alumne</th>
                    <th>Dates</th>
                    <th>Estat</th>
                    <th>Informacio</th>
                    <th>Gestio</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($incidents as $incident): ?>
                <tr>
                    <td><?= (int) $incident['id'] ?></td>
                    <td>
                        <?= h($incident['tipus'] . ' - ' . $incident['model']) ?>
                        <div class="muted"><?= h($incident['material']) ?></div>
                    </td>
                    <td>
                        <a href="<?= h(url('professorat/alumne_detall.php?id=' . (int) $incident['idAlumne'])) ?>"><?= h($incident['alumne']) ?></a>
                        <div class="muted"><?= h($incident['grupClasse']) ?></div>
                    </td>
                    <td>
                        <div>Oberta: <?= h($incident['dataOberta']) ?></div>
                        <div>Tancada: <?= display_date($incident['dataTancada']) ?></div>
                    </td>
                    <td><span class="status-badge <?= h(status_class($incident['estat'])) ?>"><?= h($incident['estat'] ?? 'Sense estat') ?></span></td>
                    <td><?= h(excerpt($incident['informacio'], 140)) ?></td>
                    <td>
                        <form method="post" class="compact-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int) $incident['id'] ?>">
                            <select name="idEstat" aria-label="Estat incidencia">
                                <?php foreach ($states as $state): ?>
                                    <option value="<?= (int) $state['id'] ?>" <?= (int) $incident['idEstat'] === (int) $state['id'] ? 'selected' : '' ?>>
                                        <?= h($state['estat']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input name="dataTancada" type="date" value="<?= h($incident['dataTancada'] ?? '') ?>">
                            <button class="btn btn-small btn-primary" type="submit">Guardar</button>
                        </form>
                        <?php if (empty($incident['dataTancada'])): ?>
                            <form method="post" data-confirm="Vols tancar aquesta incidencia?" style="margin-top: 0.45rem;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="close">
                                <input type="hidden" name="id" value="<?= (int) $incident['id'] ?>">
                                <button class="btn btn-small btn-success" type="submit">Tancar</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" data-confirm="Vols eliminar aquesta incidencia?" style="margin-top: 0.45rem;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $incident['id'] ?>">
                            <button class="btn btn-small btn-danger" type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($incidents)): ?>
                <tr><td colspan="7">No hi ha incidencies per mostrar.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

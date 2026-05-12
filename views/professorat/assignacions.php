<?php
/**
 * Gestio d'assignacions de material a alumnat.
 *
 * @var array $alumnes Alumnes disponibles per crear assignacions.
 * @var array $availableMaterial Material lliure que es pot assignar.
 * @var array $assignments Assignacions filtrades o actives.
 * @var array $tipusMaterial Tipus de material per al filtre.
 * @var array $filters Valors actuals dels filtres de consulta.
 */
?>
<section class="page-heading" style="background:none;backdrop-filter:none;border:none;box-shadow:none;padding:0.25rem 0 1.5rem;">
    <div>
        <h1 style="font-size:clamp(1.1rem,2vw,1.5rem);background:none;color:var(--snow);-webkit-text-fill-color:var(--snow);font-weight:700;margin:0;">Assignacions</h1>
        <p>Material assignat actualment a alumnat i dispositius disponibles.</p>
    </div>
</section>

<section class="grid two">
    <article class="card">
        <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Crear assignacio</h2>
        <form method="post" class="form-grid wide">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
            <div class="field">
                <label for="idAlumne">Alumne</label>
                <select id="idAlumne" name="idAlumne" required>
                    <option value="">Selecciona alumne</option>
                    <?php foreach ($alumnes as $alumne): ?>
                        <option value="<?= (int) $alumne['id'] ?>">
                            <?= h(student_full_name($alumne) . ' (' . $alumne['grupClasse'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="idMaterial">Material disponible</label>
                <select id="idMaterial" name="idMaterial" required>
                    <option value="">Selecciona material</option>
                    <?php foreach ($availableMaterial as $material): ?>
                        <option value="<?= (int) $material['id'] ?>">
                            <?= h($material['tipus'] . ' - ' . $material['model'] . ' - ' . material_label($material)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="dataInici">Data inici</label>
                <input id="dataInici" name="dataInici" type="date" value="<?= h(date('Y-m-d')) ?>">
            </div>
            <div class="field">
                <label for="dataFinal">Data final</label>
                <input id="dataFinal" name="dataFinal" type="date">
            </div>
            <button class="btn btn-primary" type="submit">Crear</button>
        </form>
    </article>

    <article class="card">
        <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Filtres</h2>
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
                <label for="alumne">Alumne</label>
                <select id="alumne" name="alumne">
                    <option value="0">Tots</option>
                    <?php foreach ($alumnes as $alumne): ?>
                        <option value="<?= (int) $alumne['id'] ?>" <?= $filters['alumne'] === (int) $alumne['id'] ? 'selected' : '' ?>>
                            <?= h(student_full_name($alumne)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="estat">Estat</label>
                <select id="estat" name="estat">
                    <option value="">Tots</option>
                    <option value="assignat" <?= $filters['estat'] === 'assignat' ? 'selected' : '' ?>>Assignat</option>
                    <option value="lliure" <?= $filters['estat'] === 'lliure' ? 'selected' : '' ?>>Sense assignar</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Filtrar</button>
        </form>
    </article>
</section>

<section class="panel" style="margin-top: 1rem;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tipus</th>
                    <th>Material</th>
                    <th>Aula</th>
                    <th>Assignat a</th>
                    <th>Data inici</th>
                    <th>Estat</th>
                    <th>Accio</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($assignments as $row): ?>
                <tr>
                    <td><?= h($row['tipus'] . ' - ' . $row['model']) ?></td>
                    <td><?= h(material_label($row)) ?></td>
                    <td><?= h($row['ubicacio']) ?></td>
                    <td>
                        <?php if ($row['idAlumne']): ?>
                            <a href="<?= h(url('professorat/alumne_detall.php?id=' . (int) $row['idAlumne'])) ?>"><?= h($row['alumne']) ?></a>
                            <div class="muted"><?= h($row['grupClasse']) ?></div>
                        <?php else: ?>
                            <span class="muted">Sense assignar</span>
                        <?php endif; ?>
                    </td>
                    <td><?= display_date($row['dataInici']) ?></td>
                    <td>
                        <?php if ($row['idAssignacio']): ?>
                            <span class="status-badge status-ok">Activa</span>
                        <?php else: ?>
                            <span class="status-badge status-warning">Disponible</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['idAssignacio']): ?>
                            <form method="post" class="compact-form" data-confirm="Vols tancar aquesta assignacio?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="close">
                                <input type="hidden" name="id" value="<?= (int) $row['idAssignacio'] ?>">
                                <input name="dataFinal" type="date" value="<?= h(date('Y-m-d')) ?>">
                                <button class="btn btn-small btn-success" type="submit">Tancar</button>
                            </form>
                            <form method="post" data-confirm="Vols eliminar aquesta assignacio?" style="margin-top: 0.45rem;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $row['idAssignacio'] ?>">
                                <button class="btn btn-small btn-danger" type="submit">Eliminar</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">Cap accio</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($assignments)): ?>
                <tr><td colspan="7">No hi ha resultats.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

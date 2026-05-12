<?php
/**
 * Detall d'un alumne concret amb formularis d'edicio i assignacio de material.
 *
 * @var array $alumne Dades de l'alumne seleccionat.
 * @var array $availableMaterial Material encara disponible per assignar.
 * @var array $assignments Historial i assignacions actuals de l'alumne.
 * @var array $incidents Incidencies vinculades a l'alumne.
 */
?>
<section class="page-heading" style="background:none;backdrop-filter:none;border:none;box-shadow:none;padding:0.25rem 0 1.5rem;">
    <div>
        <h1 style="font-size:clamp(1.1rem,2vw,1.5rem);background:none;color:var(--snow);-webkit-text-fill-color:var(--snow);font-weight:700;margin:0;"><?= h(student_full_name($alumne)) ?></h1>
        <p><?= h($alumne['grupClasse']) ?> · <?= h($alumne['correu']) ?></p>
    </div>
    <a class="btn btn-secondary" href="<?= h(url('professorat/alumnes.php')) ?>">Tornar a alumnes</a>
</section>

<section class="grid two">
    <article class="card">
        <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Dades de l'alumne</h2>
        <form method="post" class="form-grid wide">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_student">
            <input type="hidden" name="idAlumne" value="<?= (int) $alumne['id'] ?>">
            <div class="field">
                <label for="nom">Nom</label>
                <input id="nom" name="nom" value="<?= h($alumne['nom']) ?>" required>
            </div>
            <div class="field">
                <label for="cognom1">Primer cognom</label>
                <input id="cognom1" name="cognom1" value="<?= h($alumne['cognom1']) ?>" required>
            </div>
            <div class="field">
                <label for="cognom2">Segon cognom</label>
                <input id="cognom2" name="cognom2" value="<?= h($alumne['cognom2'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="correu">Correu</label>
                <input id="correu" name="correu" type="email" value="<?= h($alumne['correu']) ?>" required>
            </div>
            <div class="field">
                <label for="grupClasse">Grup</label>
                <input id="grupClasse" name="grupClasse" value="<?= h($alumne['grupClasse']) ?>" required>
            </div>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </form>
    </article>

    <article class="card">
        <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Assignar material</h2>
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create_assignment">
            <input type="hidden" name="idAlumne" value="<?= (int) $alumne['id'] ?>">
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
            <button class="btn btn-primary" type="submit">Assignar</button>
        </form>
    </article>
</section>

<section class="panel" style="margin-top: 1rem;">
    <div class="card" style="padding:0.85rem 1.45rem;border-bottom:1px solid rgba(148,163,184,0.1);">
        <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Dispositius de l'alumne</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Aula</th>
                    <th>Data inici</th>
                    <th>Data final</th>
                    <th>Estat</th>
                    <th>Accio</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($assignments as $assignment): ?>
                <tr>
                    <td>
                        <?= h($assignment['tipus'] . ' - ' . $assignment['model']) ?>
                        <div class="muted"><?= h(material_label($assignment)) ?></div>
                    </td>
                    <td><?= h($assignment['ubicacio']) ?></td>
                    <td><?= h($assignment['dataInici']) ?></td>
                    <td><?= display_date($assignment['dataFinal']) ?></td>
                    <td>
                        <?php if (!empty($assignment['estatIncidencia'])): ?>
                            <span class="status-badge <?= h(status_class($assignment['estatIncidencia'])) ?>"><?= h($assignment['estatIncidencia']) ?></span>
                        <?php elseif (assignment_is_active($assignment)): ?>
                            <span class="status-badge status-ok">Activa</span>
                        <?php else: ?>
                            <span class="status-badge status-warning">Retornada</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (assignment_is_active($assignment)): ?>
                            <form method="post" class="compact-form" data-confirm="Vols tancar aquesta assignacio?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="close_assignment">
                                <input type="hidden" name="idAlumne" value="<?= (int) $alumne['id'] ?>">
                                <input type="hidden" name="idAssignacio" value="<?= (int) $assignment['idAssignacio'] ?>">
                                <input name="dataFinal" type="date" value="<?= h(date('Y-m-d')) ?>">
                                <button class="btn btn-small btn-success" type="submit">Marcar retorn</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" data-confirm="Vols eliminar aquesta assignacio?" style="margin-top: 0.45rem;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_assignment">
                            <input type="hidden" name="idAlumne" value="<?= (int) $alumne['id'] ?>">
                            <input type="hidden" name="idAssignacio" value="<?= (int) $assignment['idAssignacio'] ?>">
                            <button class="btn btn-small btn-danger" type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($assignments)): ?>
                <tr><td colspan="6">Aquest alumne no te dispositius assignats.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel" style="margin-top: 1rem;">
    <div class="card" style="padding:0.85rem 1.45rem;border-bottom:1px solid rgba(148,163,184,0.1);">
        <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;">Incidencies vinculades</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Data oberta</th>
                    <th>Data tancada</th>
                    <th>Estat</th>
                    <th>Informacio</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($incidents as $incident): ?>
                <tr>
                    <td><?= h($incident['material']) ?></td>
                    <td><?= h($incident['dataOberta']) ?></td>
                    <td><?= display_date($incident['dataTancada']) ?></td>
                    <td><span class="status-badge <?= h(status_class($incident['estat'])) ?>"><?= h($incident['estat'] ?? 'Sense estat') ?></span></td>
                    <td><?= h(excerpt($incident['informacio'], 150)) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($incidents)): ?>
                <tr><td colspan="5">No hi ha incidencies vinculades a aquest alumne.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

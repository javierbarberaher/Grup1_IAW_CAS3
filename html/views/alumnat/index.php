<?php
/**
 * Panell de l'alumne amb el seu material assignat i les incidencies associades.
 *
 * @var array $alumne Dades de l'alumne autenticat.
 * @var array $assignments Llista d'assignacions de material.
 * @var array $incidents Llista d'incidencies relacionades amb l'alumne.
 */
?>
<section class="page-heading" style="background: none; backdrop-filter: none; border: none; box-shadow: none; padding: 0.25rem 0 1.5rem;">
    <div>
        <p style="margin: 0 0 0.25rem; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--mist);"><?= h($alumne['grupClasse']) ?></p>
        <h1 style="font-size: clamp(1.2rem, 2.5vw, 1.65rem); background: none; color: var(--snow); -webkit-text-fill-color: var(--snow);"><?= h(student_full_name($alumne)) ?></h1>
    </div>
</section>

<section class="panel" style="margin-bottom: 1rem;">
    <div class="card" style="padding: 1rem 1.45rem 0.75rem; border-bottom: 1px solid rgba(148,163,184,0.1);">
        <h2 class="section-title" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--mist); margin-bottom: 0;">Material assignat</h2>
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
                            <span class="status-badge status-ok">Actiu</span>
                        <?php else: ?>
                            <span class="status-badge status-warning">Retornat</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($assignments)): ?>
                <tr><td colspan="5">No tens dispositius assignats.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="card" style="padding: 1rem 1.45rem 0.75rem; border-bottom: 1px solid rgba(148,163,184,0.1);">
        <h2 class="section-title" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--mist); margin-bottom: 0;">Incidències</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Data oberta</th>
                    <th>Data tancada</th>
                    <th>Estat</th>
                    <th>Informació</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($incidents as $incident): ?>
                <tr>
                    <td>
                        <?= h($incident['tipus'] . ' - ' . $incident['model']) ?>
                        <div class="muted"><?= h($incident['material']) ?></div>
                    </td>
                    <td><?= h($incident['dataOberta']) ?></td>
                    <td><?= display_date($incident['dataTancada']) ?></td>
                    <td><span class="status-badge <?= h(status_class($incident['estat'])) ?>"><?= h($incident['estat'] ?? 'Sense estat') ?></span></td>
                    <td><?= h(excerpt($incident['informacio'], 160)) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($incidents)): ?>
                <tr><td colspan="5">No tens incidències registrades.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

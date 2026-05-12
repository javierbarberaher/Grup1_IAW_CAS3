<?php
/**
 * Gestio de comptes d'usuari per a professorat i alumnat.
 *
 * @var array $users Comptes existents.
 * @var array $students Alumnes que es poden vincular a un compte.
 * @var array|null $editUser Usuari carregat per editar o null per crear.
 */
?>
<section class="page-heading" style="background:none;backdrop-filter:none;border:none;box-shadow:none;padding:0.25rem 0 1.5rem;">
    <div>
        <h1 style="font-size:clamp(1.1rem,2vw,1.5rem);background:none;color:var(--snow);-webkit-text-fill-color:var(--snow);font-weight:700;margin:0;">Usuaris</h1>
        <p>Comptes d'acces per a professorat i alumnat.</p>
    </div>
</section>

<section class="card">
    <h2 class="section-title" style="font-size:0.68rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:var(--mist);margin:0;"><?= $editUser ? 'Editar usuari' : 'Nou usuari' ?></h2>
    <form method="post" class="form-grid wide">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $editUser ? 'update' : 'create' ?>">
        <?php if ($editUser): ?>
            <input type="hidden" name="id" value="<?= (int) $editUser['id'] ?>">
        <?php endif; ?>
        <div class="field">
            <label for="nom">Nom</label>
            <input id="nom" name="nom" value="<?= h($editUser['nom'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label for="cognom1">Primer cognom</label>
            <input id="cognom1" name="cognom1" value="<?= h($editUser['cognom1'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label for="cognom2">Segon cognom</label>
            <input id="cognom2" name="cognom2" value="<?= h($editUser['cognom2'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="correu">Correu</label>
            <input id="correu" name="correu" type="email" value="<?= h($editUser['correu'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label for="rol">Rol</label>
            <select id="rol" name="rol">
                <option value="<?= h(ROLE_STUDENT) ?>" <?= ($editUser['rol'] ?? '') === ROLE_STUDENT ? 'selected' : '' ?>>Alumnat</option>
                <option value="<?= h(ROLE_PROFESSOR) ?>" <?= ($editUser['rol'] ?? '') === ROLE_PROFESSOR ? 'selected' : '' ?>>Professorat</option>
            </select>
        </div>
        <div class="field">
            <label for="idAlumne">Alumne vinculat</label>
            <select id="idAlumne" name="idAlumne">
                <option value="">Sense vincle</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= (int) $student['id'] ?>" <?= (int) ($editUser['idAlumne'] ?? 0) === (int) $student['id'] ? 'selected' : '' ?>>
                        <?= h(student_full_name($student) . ' (' . $student['grupClasse'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="password"><?= $editUser ? 'Nova contrasenya' : 'Contrasenya' ?></label>
            <input id="password" name="password" type="password" <?= $editUser ? '' : 'required' ?>>
        </div>
        <label class="field">
            <span>Compte actiu</span>
            <input type="checkbox" name="actiu" value="1" <?= (int) ($editUser['actiu'] ?? 1) === 1 ? 'checked' : '' ?>>
        </label>
        <div class="actions">
            <button class="btn btn-primary" type="submit"><?= $editUser ? 'Guardar canvis' : 'Crear usuari' ?></button>
            <?php if ($editUser): ?>
                <a class="btn btn-secondary" href="<?= h(url('professorat/usuaris.php')) ?>">Cancel.lar</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="panel" style="margin-top: 1rem;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Usuari</th>
                    <th>Rol</th>
                    <th>Alumne vinculat</th>
                    <th>Estat</th>
                    <th>Accions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $account): ?>
                <tr>
                    <td>
                        <?= h(trim($account['nom'] . ' ' . $account['cognom1'])) ?>
                        <div class="muted"><?= h($account['correu']) ?></div>
                    </td>
                    <td><?= h(role_label($account['rol'])) ?></td>
                    <td><?= display_value($account['alumne']) ?></td>
                    <td>
                        <?php if ((int) $account['actiu'] === 1): ?>
                            <span class="status-badge status-ok">Actiu</span>
                        <?php else: ?>
                            <span class="status-badge status-danger">Inactiu</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a class="btn btn-small btn-primary" href="<?= h(url('professorat/usuaris.php?edit=' . (int) $account['id'])) ?>">Editar</a>
                        <form method="post" data-confirm="Vols eliminar aquest usuari?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $account['id'] ?>">
                            <button class="btn btn-small btn-danger" type="submit" <?= (int) $currentUser['id'] === (int) $account['id'] ? 'disabled' : '' ?>>Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr><td colspan="5">No hi ha usuaris creats.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

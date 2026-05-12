<?php
/**
 * Missatges temporals mostrats una sola vegada despres d'una accio.
 *
 * @var array $flashMessages Llista de missatges amb type i message.
 */
if (empty($flashMessages ?? [])) {
    return;
}
?>
<section class="flash-area" aria-live="polite" style="padding-top: 0.5rem;">
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert-<?= h($flash['type'] ?? 'info') ?>" style="
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.6rem 0.9rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        "><?= h($flash['message'] ?? '') ?></div>
    <?php endforeach; ?>
</section>

<?php $stepLabels = ['Cart', 'Checkout', 'Payment', 'Receipt']; ?>
<ol class="tg-steps" aria-label="Checkout progress">
<?php foreach ($stepLabels as $i => $label): $n = $i + 1; ?>
    <li class="<?= $n < $stepCurrent ? 'is-done' : ($n === $stepCurrent ? 'is-current' : '') ?>"<?= $n === $stepCurrent ? ' aria-current="step"' : '' ?>><span class="tg-step-dot"><?= $n < $stepCurrent ? tgh_icon('check') : $n ?></span><span class="tg-step-label"><?= tgh_e($label) ?></span></li>
<?php endforeach; ?>
</ol>

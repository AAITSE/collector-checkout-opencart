<?php
/** @var ModelCollectorView $view */
/** @var array $totals */
?>
<table class="table table-responsive table-sm table-hover">
    <tbody>
    <?php foreach ($totals as $total): ?>
    <tr>
        <td colspan="1">
            <strong><?php echo $total['title']; ?></strong>
        </td>
        <td>
            <strong><?php echo $view->format($total['total']); ?></strong>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php
/** @var ModelCollectorView $view */
/** @var array $methods */
/** @var string|bool $code */
?>

<?php foreach ($methods as $shipping_method): ?>
    <p>
        <strong><?php echo $shipping_method['title']; ?></strong>
    </p>

    <?php if (!$shipping_method['error']): ?>
        <?php foreach ($shipping_method['quote'] as $quote): ?>
            <div class="radio">
                <label>
                    <?php if ($quote['code'] == $code || !$code): ?>
                        <?php $code = $quote['code']; ?>
                        <input type="radio" name="shipping_method" value="<?php echo $quote['code']; ?>" checked="checked" />
                    <?php else: ?>
                        <input type="radio" name="shipping_method" value="<?php echo $quote['code']; ?>" />
                    <?php endif; ?>
                    <?php echo $quote['title']; ?> - <?php echo $quote['text']; ?></label>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-danger"><?php echo $shipping_method['error']; ?></div>
    <?php endif; ?>
<?php endforeach; ?>

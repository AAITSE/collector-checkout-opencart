<?php
/** @var ModelCollectorView $view */
/** @var array $products */
?>
<div class="collector-cart">
    <table class="table table-responsive table-hover">
        <thead>
        <tr>
            <td class="text-center"><?php echo $view->__('Image'); ?></td>
            <td class="text-left"><?php echo $view->__('Name'); ?></td>
            <td class="text-left"><?php echo $view->__('Quantity'); ?></td>
            <td class="text-right"><?php echo $view->__('Price'); ?></td>
            <td class="text-right"><?php echo $view->__('Total'); ?></td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr id="cart_<?php echo $product['cart_id']; ?>" data-cart-id="<?php echo $product['cart_id']; ?>">
                <td class="text-center">
                    <?php if ($product['thumb']): ?>
                        <a href="<?php echo $product['href']; ?>">
                            <img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>"
                                 title="<?php echo $product['name']; ?>" class="img-thumbnail"/>
                        </a>
                    <?php endif; ?>
                </td>

                <td class="text-left">
                    <a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
                </td>

                <td class="text-left">
                    <div class="input-group btn-block" style="max-width: 200px;">
                        <input type="number" min="1" step="1"
                               name="quantity[<?php echo $product['cart_id']; ?>]"
                               value="<?php echo $product['qty']; ?>"
                               style="width: 50px;"
                               size="1" class="form-control qty"/>
                        <button type="button" title="<?php echo $view->__('Remove'); ?>" class="btn btn-danger btn-xs remove">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </td>

                <td class="text-right">
                        <span class="unit-price">
                            <?php echo $view->format($product['price_with_tax'] / $product['qty']); ?>
                        </span>
                </td>
                <td class="text-right">
                        <span class="total-price">
                            <?php echo $view->format($product['price_with_tax']); ?>
                        </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

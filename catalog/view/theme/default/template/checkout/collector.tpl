<?php
/** @var ModelCollectorView $view */
/** @var string $header */
/** @var string $footer */
/** @var array $breadcrumbs */
/** @var string $error_warning */
/** @var string $column_left */
/** @var string $column_right */
/** @var string $content_top */

/** @var string $collector */
?>

<!-- Start header -->
<?php echo $header; ?>
<!-- End header -->

    <div class="container">
        <!-- Start breadcrumb -->
        <ul class="breadcrumb">
            <?php foreach ($breadcrumbs as $breadcrumb): ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
            <?php endforeach; ?>
        </ul>
        <!-- End breadcrumb -->

        <div class="row">
            <?php echo $column_left; ?>

            <?php if ($column_left && $column_right): ?>
                <?php $class = 'col-sm-6'; ?>
            <?php elseif ($column_left || $column_right): ?>
                <?php $class = 'col-sm-9'; ?>
            <?php else: ?>
                <?php $class = 'col-sm-12'; ?>
            <?php endif; ?>

            <div id="content" class="<?php echo $class; ?>">
                <!-- Start Content -->
                <?php echo $content_top; ?>

                <h1><?php echo $view->__('Checkout'); ?></h1>
                <div class="collector-checkout">
                    <?php if (!$is_logged): ?>
                        <?php if (version_compare(VERSION, '2.3.0.0', '=>')): ?>
                            <?php echo $view->render('checkout/collector/account.tpl', $data); ?>
                        <?php else: ?>
                            <?php echo $view->render('default/template/checkout/collector/account.tpl', $data); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <h2><?php echo $view->__('Cart'); ?></h2>
                    <div id="collector-cart" class="row">&nbsp</div>
                    <div id="collector-totals" class="row">&nbsp</div>
                    <div id="collector-coupon" class="row">
                        <?php //echo $view->render('checkout/collector/coupon', $data); ?>
                    </div>

                    <?php if ($store_mode == ''): ?>
                    <div id="collector-customer" class="row">
                        <?php if ($customer_type === 'private'): ?>
                        <button id="change-customer" type="button" class="btn btn btn-secondary btn-xs" data-customer="company">
                            <?php echo $view->__('Change to business customer'); ?>
                        </button>
                        <?php else: ?>
                            <button id="change-customer" type="button" class="btn btn btn-secondary btn-xs" data-customer="private">
                                <?php echo $view->__('Change to private customer'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <h2><?php echo $view->__('Select country'); ?></h2>
                    <div id="collector-countries" class="row">
                        <div class="form-group required">
                            <select name="country_id" id="input-payment-country" class="form-control">
                                <option value=""><?php echo $view->__('Please select'); ?></option>
                                <?php foreach ($countries as $country): ?>
                                    <?php if ($country['country_id'] == $country_id): ?>
                                        <option value="<?php echo $country['country_id']; ?>" selected="selected">
                                            <?php echo $country['name']; ?>
                                        </option>
                                    <?php else: ?>
                                        <option value="<?php echo $country['country_id']; ?>">
                                            <?php echo $country['name']; ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($shipping_required): ?>
                        <h2><?php echo $view->__('Shipping Method'); ?></h2>
                        <div id="collector-shipping" class="row">&nbsp;</div>
                    <?php endif; ?>

                    <h2><?php echo $view->__('Place Order'); ?></h2>
                    <div id="collector-checkout" class="row">
                        <?php if ($customer_type === 'private'): ?>
                            <script src="<?php echo $collector['frontend_api_url']; ?>/collector-checkout-loader.js" data-token="<?php echo $collector['token']; ?>" data-lang="<?php echo $locale; ?>" data-padding="none"></script>
                        <?php else: ?>
                            <script src="<?php echo $collector['frontend_api_url']; ?>/collector-checkout-loader.js" data-variant="b2b" data-token="<?php echo $collector['token']; ?>" data-lang="<?php echo $locale; ?>" data-padding="none"></script>
                        <?php endif;?>
                    </div>

                <?php echo $content_bottom; ?>
                <!-- End Content -->
            </div>
            <?php echo $column_right; ?>
        </div>
    </div>

<!-- Start footer -->
<?php echo $footer; ?>
<!-- Start footer -->

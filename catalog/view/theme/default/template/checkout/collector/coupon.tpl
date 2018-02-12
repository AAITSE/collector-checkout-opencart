<?php
/** @var ModelCollectorView $view */
/** @var array $products */
?>
<div class="well">
    <?php echo sprintf($view->__('Have coupon? Click <a class="coupon-link" href="%s">here</a> to apply'), '#'); ?>
    <script>
        $(document).on('click', 'a.coupon-link', function(e) {
            e.preventDefault();
            $('#coupon-form').slideDown();
            return false;
        });
    </script>

    <div id="coupon-form" style="display: none;">
        <p><strong><?php echo $view->__('If you have an account with us, please log in.'); ?></strong></p>
        <form id="account-login" action="<?php echo $view->url('account/login'); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label class="control-label" for="input-email">
                    <?php echo $view->__('E-Mail Address'); ?>
                </label>
                <input type="text" name="email" value="" placeholder="<?php echo $view->__('E-Mail Address'); ?> " id="input-email" class="form-control">
            </div>

            <div class="form-group">
                <label class="control-label" for="input-password">
                    <?php echo $view->__('Password'); ?>
                </label>
                <input type="password" name="password" value="" placeholder="<?php echo $view->__('Password'); ?>" id="input-password" class="form-control">

                <a href="<?php echo $view->url('account/forgotten'); ?>">
                    <?php echo $view->__('Forgotten Password'); ?>
                </a>
            </div>
            <input type="submit" value="Login" class="btn btn-primary">
            <input type="hidden" name="redirect" value="<?php ?>">
        </form>
    </div>
</div>

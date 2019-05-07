<?php echo $header; ?>
<?php echo $column_left; ?>

    <div id="content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="pull-right">
                    <button type="submit" form="form-cardinity" data-toggle="tooltip"
                            title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i>
                    </button>
                    <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                       class="btn btn-default">
                        <i class="fa fa-reply"></i>
                    </a>
                </div>
                <h1><?php echo $heading_title; ?></h1>
                <ul class="breadcrumb">
                    <?php foreach ($breadcrumbs as $breadcrumb): ?>
                        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
                </div>
                <div class="panel-body">
                    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-collector" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_status">
                                <?php echo $entry_status; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="collector_status" id="collector_status" class="form-control">
                                    <?php if ($collector_status): ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php else: ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_store_mode">
                                Store Mode
                            </label>
                            <div class="col-sm-10">
                                <select name="collector_store_mode" id="collector_store_mode" class="form-control">
                                    <option value="" <?php echo $collector_store_mode == '' ? 'selected' : ''; ?>>B2C and B2B</option>
                                    <option value="b2c" <?php echo $collector_store_mode === 'b2c' ? 'selected' : ''; ?>>B2C Only</option>
                                    <option value="b2b" <?php echo $collector_store_mode === 'b2b' ? 'selected' : ''; ?>>B2B Only</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_country">
                                Country
                            </label>
                            <div class="col-sm-10">
                                <select name="collector_country" id="collector_country" class="form-control">
                                    <option value="" <?php echo $collector_country == '' ? 'selected' : ''; ?>>Sweden and Norway</option>
                                    <option value="SE" <?php echo $collector_country === 'SE' ? 'selected' : ''; ?>>Sweden Only</option>
                                    <option value="NO" <?php echo $collector_country === 'NO' ? 'selected' : ''; ?>>Norway Only</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_store_id_b2c_se">
                                Merchant ID (B2C, Sweden)
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="collector_store_id_b2c_se" value="<?php echo $collector_store_id_b2c_se; ?>" id="input-key" class="form-control"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_store_id_b2b_se">
                                Merchant ID (B2B, Sweden)
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="collector_store_id_b2b_se" value="<?php echo $collector_store_id_b2b_se; ?>" id="input-key" class="form-control"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_store_id_b2c_no">
                                Merchant ID (B2C, Norway)
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="collector_store_id_b2c_no" value="<?php echo $collector_store_id_b2c_no; ?>" id="input-key" class="form-control"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_store_id_b2b_no">
                                Merchant ID (B2B, Norway)
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="collector_store_id_b2b_no" value="<?php echo $collector_store_id_b2b_no; ?>" id="input-key" class="form-control"/>
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="collector_username">
                                <?php echo $entry_username; ?>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="collector_username" value="<?php echo $collector_username; ?>"
                                       placeholder="<?php echo $entry_username; ?>" id="input-key" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="collector_sharedkey">
                                <?php echo $entry_sharedkey; ?>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="collector_sharedkey" value="<?php echo $collector_sharedkey; ?>"
                                       placeholder="<?php echo $entry_sharedkey; ?>" id="input-key" class="form-control" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_mode">
                                <?php echo $entry_mode; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="collector_mode" id="collector_mode" class="form-control">
                                    <option value="live" <?php echo $collector_mode === 'live' ? 'selected="selected"' : ''; ?> >Production</option>
                                    <option value="test" <?php echo $collector_mode === 'test' ? 'selected="selected"' : ''; ?>>Test</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_order_status_accepted_id">
                                <?php echo $entry_order_status_accepted; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="collector_order_status_accepted_id" id="collector_order_status_accepted_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status): ?>
                                        <?php if ($order_status['order_status_id'] == $collector_order_status_accepted_id): ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"
                                                    selected="selected"><?php echo $order_status['name']; ?>
                                            </option>
                                        <?php else: ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>">
                                                <?php echo $order_status['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_order_status_preliminary_id">
			                    <?php echo $entry_order_status_preliminary; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="collector_order_status_preliminary_id" id="collector_order_status_preliminary_id" class="form-control">
				                    <?php foreach ($order_statuses as $order_status): ?>
					                    <?php if ($order_status['order_status_id'] == $collector_order_status_preliminary_id): ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"
                                                    selected="selected"><?php echo $order_status['name']; ?>
                                            </option>
					                    <?php else: ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>">
							                    <?php echo $order_status['name']; ?>
                                            </option>
					                    <?php endif; ?>
				                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_order_status_pending_id">
                                <?php echo $entry_order_status_pending; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="collector_order_status_pending_id" id="collector_order_status_pending_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status): ?>
                                        <?php if ($order_status['order_status_id'] == $collector_order_status_pending_id): ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"
                                                    selected="selected"><?php echo $order_status['name']; ?>
                                            </option>
                                        <?php else: ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>">
                                                <?php echo $order_status['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_order_status_rejected_id">
                                <?php echo $entry_order_status_rejected; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="collector_order_status_rejected_id" id="collector_order_status_rejected_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status): ?>
                                        <?php if ($order_status['order_status_id'] == $collector_order_status_rejected_id): ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"
                                                    selected="selected"><?php echo $order_status['name']; ?>
                                            </option>
                                        <?php else: ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>">
                                                <?php echo $order_status['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_order_status_credited_id">
			                    <?php echo $entry_order_status_credited; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="collector_order_status_credited_id" id="collector_order_status_credited_id" class="form-control">
				                    <?php foreach ($order_statuses as $order_status): ?>
					                    <?php if ($order_status['order_status_id'] == $collector_order_status_credited_id): ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"
                                                    selected="selected"><?php echo $order_status['name']; ?>
                                            </option>
					                    <?php else: ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>">
							                    <?php echo $order_status['name']; ?>
                                            </option>
					                    <?php endif; ?>
				                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="collector_merchant_terms_url">
                                <?php echo $entry_merchant_terms_url; ?>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="collector_merchant_terms_url" value="<?php echo $collector_merchant_terms_url; ?>"
                                       placeholder="<?php echo $entry_merchant_terms_url; ?>" id="collector_merchant_terms_url" class="form-control" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_invoice_fee_b2c">
                                <?php echo $entry_invoice_fee_b2c; ?>
                            </label>
                            <div class="col-sm-10">
                                <input type="number" min="0" name="collector_invoice_fee_b2c" value="<?php echo $collector_invoice_fee_b2c; ?>"
                                       placeholder="<?php echo $entry_invoice_fee_b2c; ?>" id="collector_invoice_fee_b2c" class="form-control" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_invoice_fee_vat_b2c">
                                <?php echo $entry_invoice_fee_vat_b2c; ?>
                            </label>
                            <div class="col-sm-10">
                                <input type="number" min="0" name="collector_invoice_fee_vat_b2c" value="<?php echo $collector_invoice_fee_vat_b2c; ?>"
                                       placeholder="<?php echo $entry_invoice_fee_vat_b2c; ?>" id="collector_invoice_fee_vat_b2c" class="form-control" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_invoice_fee_b2b">
                                <?php echo $entry_invoice_fee_b2b; ?>
                            </label>
                            <div class="col-sm-10">
                                <input type="number" min="0" name="collector_invoice_fee_b2b" value="<?php echo $collector_invoice_fee_b2c; ?>"
                                       placeholder="<?php echo $entry_invoice_fee_b2b; ?>" id="collector_invoice_fee_b2b" class="form-control" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="collector_invoice_fee_vat_b2b">
                                <?php echo $entry_invoice_fee_vat_b2b; ?>
                            </label>
                            <div class="col-sm-10">
                                <input type="number" min="0" name="collector_invoice_fee_vat_b2b" value="<?php echo $collector_invoice_fee_vat_b2b; ?>"
                                       placeholder="<?php echo $entry_invoice_fee_vat_b2b; ?>" id="collector_invoice_fee_vat_b2b" class="form-control" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php echo $footer; ?>
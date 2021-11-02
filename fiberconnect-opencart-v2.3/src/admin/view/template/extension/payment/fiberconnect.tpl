<?php echo $header; ?> <?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-fiberconnect_ageneral" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="alert alert-info">
      <i class="fa fa-exclamation-circle"></i>
      <?php echo $tips_fiberconnect; ?>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $edit_title; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-fiberconnect_ageneral" class="form-horizontal">
          <h4><?php echo $settings_connection; ?></h4>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-api-key"><?php echo $settings_connection_key; ?></label>
            <div class="col-sm-10">
              <input type="password" name="fiberconnect_api_key" value="<?php echo $fiberconnect_api_key; ?>" placeholder="<?php echo $settings_connection_key; ?>" id="input-api-key" class="form-control" />
              <?php if ($error_api_key) { ?>
              <span class="error-message"><?php echo $error_api_key; ?></span>
              <?php } ?>
            </div>
          </div>

          <h4><?php echo $settings_basic; ?></h4>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-title"><span data-toggle="tooltip" title="<?php echo $settings_basic_name_tips; ?>"><?php echo $settings_basic_name; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="fiberconnect_title" value="<?php echo $fiberconnect_title; ?>" placeholder="<?php echo $settings_basic_name; ?>" id="input-title" class="form-control" />
              <?php if ($error_title) { ?>
              <span class="error-message"><?php echo $error_title; ?></span>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><span data-toggle="tooltip" title="<?php echo $settings_basic_status_tips; ?>"><?php echo $settings_basic_status; ?></span></label>
            <div class="col-sm-10">
              <select name="fiberconnect_status" id="input-status" class="form-control">
                <?php if ($fiberconnect_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><span data-toggle="tooltip" title="<?php echo $settings_basic_order_tips; ?>"><?php echo $settings_basic_order; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="fiberconnect_sort_order" value="<?php echo $fiberconnect_sort_order; ?>" placeholder="<?php echo $settings_basic_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>

          <h4><?php echo $settings_payment; ?></h4>
          <div class="form-group">
            <div class="payment-message"><?php echo $settings_payment_message; ?></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(document).ready(() => {
  if ($('#input-title').val() === '') {
    $('#input-title').val('FiberConnect Payment (轉數快, PayMe, 支付寶, 微信支付)');
  }
});
</script>

<style>
h4 {
  margin-top: 15px;
  margin-left: 3px;
}

h4 + .form-group {
  border-top: 1px solid #ededed;
}

.form-group {
  margin: 0px;
  padding: 25px 0px;
}

.error-message {
  color: #ff0000;
  display: inline-block;
  margin-top: 5px;
}

.payment-message {
  color: #999999;
  font-size: 1.5rem;
  margin-bottom: -15px;
  margin-left: 15px;
}
</style>
<?php echo $footer; ?>
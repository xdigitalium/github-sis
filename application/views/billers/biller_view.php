<?php
$show_title = isset($show_title)?$show_title:true;
$show_row = isset($show_row)?$show_row:true;
$cols = isset($cols)?$cols:"col-6 col-xs-6";
?>
<?php if ($show_row): ?>
<div class="col-xs-6" style="margin-left: -15px; margin-bottom: 5px;">
<span style="display:block; background:#2e2563; color:#ffffff; width:100%; border: 1px solid #dddddd; border-bottom: 0;">Client</span>
<?php endif ?>
    <div class="col-xs-12" style="border: 1px solid #dddddd;">
        <b class="inv">
        <?php
        if( trim($biller->company) != "" ) {
            echo "<b>".utf8_encode("Société").":</b> ".$biller->company;
        } else {
            echo $biller->fullname."<br />";
        }
        ?>
        </b>
        <?php if( trim($biller->company) != "" ) { echo "<br /><b>Contact:</b> ".$biller->fullname."<br />"; } ?>
        <?php
        if( $biller->phone ){ echo "<b>".lang("phone").":</b> ".$biller->phone."<br>";}
        if( $biller->email ){ echo "<b>".lang("email").":</b> ".$biller->email."<br>"; }
        if( $biller->website ){ echo "<b>".lang("website").":</b> ".$biller->website."<br>";}
        ?>
        <?php
        if( $biller->address  ){
            echo "<b>".lang("address").":</b> ";
            echo $biller->fulladdress.",<br />";
        }
        if( $biller->vat_number ){ echo "<b>".lang("vat_number").":</b> ".$biller->vat_number."<br>";}

        $cf = $this->settings_model->SYS_Settings;
        if( $biller->custom_field1 && trim($cf->customer_cf1) != "" ){ echo "<b>".$cf->customer_cf1.":</b> ".$biller->custom_field1."<br>";}
        if( $biller->custom_field2 && trim($cf->customer_cf2) != "" ){ echo "<b>".$cf->customer_cf2.":</b> ".$biller->custom_field2."<br>";}
        if( $biller->custom_field3 && trim($cf->customer_cf3) != "" ){ echo "<b>".$cf->customer_cf3.":</b> ".$biller->custom_field3."<br>";}
        if( $biller->custom_field4 && trim($cf->customer_cf4) != "" ){ echo "<b>".$cf->customer_cf4.":</b> ".$biller->custom_field4;}
        ?>
    </div>
</div>
<div class="col-xs-3 col-xs-offset-2" style="padding: 0px;">
<span style="display:block; background:#2e2563; color:#ffffff; width:100%; border: 1px solid #dddddd;">
<?php
echo $cf->invoice_cf1;
?>
</span>
</div>
<div class="col-xs-1" style="padding: 0px;">
<span style="display:block;width:100%;text-align: center;border: 1px solid #dddddd; border-left: 0;">
<?php
echo $invoice->custom_field1;
?>
</span>
</div>
<?php if ($show_row): ?>
    <div class="clearfix"></div>
<?php endif ?>

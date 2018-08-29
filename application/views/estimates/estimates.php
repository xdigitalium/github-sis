<?php
$this->load->enqueue_script("assets/vendor/jquery-datatable/js/jquery.dataTables.min.js");
$this->load->enqueue_script("assets/vendor/jquery-datatable/js/dataTables.colVis.js");
$this->load->enqueue_script("assets/vendor/jquery-datatable/js/dataTables.advancedSearch.js");
$this->load->enqueue_script("assets/vendor/jquery-datatable/js/DT_bootstrap.js");

$status_list = array();
foreach ($this->settings_model->getEstimateStatus() as $key => $value) {
    $status_list[] = array(
        "label" => $value,
        "value" => $key
    );
}
?>
<?php if (!isset($disable_headings) || !$disable_headings): ?>
<style type="text/css">
.popover-content{padding: 0px;}
</style>
<!-- Page Header -->
<ol class="breadcrumb">
    <div class="flip pull-left">
        <h1 class="h2 page-title"><?php echo $page_title;?></h1>
        <div class="text-muted page-desc"><?php echo $page_subheading;?></div>
    </div>
    <div class="flip pull-right" style="line-height: 64px;">
        <?php if (!$this->ion_auth->in_group(array("customer", "supplier"))): ?>
        <a href="<?php echo site_url('/estimates/create');?>" class="btn btn-primary-outline tip" title="<?php echo lang("create_estimate"); ?>"> <i class="fa fa-plus"></i></a>
        <?php endif ?>
        <a href="#refresh-list" title="<?php echo lang("refresh"); ?>" class="btn btn-primary-outline tip"><i class="fa fa-refresh"></i></a>
        <div class="btn-group tip" title="<?php echo lang("filter"); ?>">
            <?php $class = (isset($_GET['f']) && $_GET['f'] == "status")?"btn-primary":"btn-primary-outline"; ?>
            <a class="btn <?php echo $class ?> dropdown-toggle" data-toggle="dropdown"><i class="fa fa-filter"></i><span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-right">
                <?php $class = isset($_GET['fv'])?"":"active"; ?>
                <a href="<?php echo site_url('/estimates');?>" class="dropdown-item <?php echo $class ?>"><?php echo lang("all") ?></a>
                <?php foreach ($this->settings_model->getEstimateStatus() as $status => $label): ?>
                    <?php $class = (isset($_GET['fv']) && $_GET['fv'] == $status)?"active":""; ?>
                    <a href="<?php echo site_url('/estimates?f=status&fv='.$status);?>" class="dropdown-item <?php echo $class ?>"><?php echo $label ?></a>
                <?php endforeach ?>
            </ul>
        </div>
        <div class="btn-group download-list tip" title="<?php echo lang("tabletool_collection"); ?>" export-table="estimates_table">
            <a class="btn btn-primary-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-download"></i><span class="caret"></span></a>
        </div>
        <div class="btn-group columns-list tip" title="<?php echo lang("shown_columns"); ?>">
            <a class="btn btn-primary-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-columns"></i><span class="caret"></span></a>
        </div>
        <div class="btn-group actions-list tip" title="<?php echo lang("actions"); ?>">
            <a class="btn btn-primary-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-link"></i><span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-right">
                <?php if (!$this->ion_auth->in_group(array("customer", "supplier"))): ?>
                <li><a href="#" class="dropdown-item disabled btn-select-multi delete_selected"><i class="fa fa-trash"></i><?php echo lang("delete") ?></a></li>
                <li class="dropdown-divider"></li>
                <?php endif ?>
                <li><a href="#" class="dropdown-item disabled btn-select-multi print_selected"><i class="fa fa-print"></i><?php echo lang("print") ?></a></li>
                <li><a href="#" class="dropdown-item disabled btn-select-multi pdf_selected"><i class="fa fa-download"></i><?php echo lang("tabletool_pdf") ?></a></li>
            </ul>
        </div>
    </div>
</ol>

<div class="container-fluid">
    <div class="col-md-12">
        <div class="card">
            <div class="card-block">
<?php endif ?>
                <table id="estimates_table" class="table table-sm table-hover serverSide checkable_datatable">
					<thead>
						<tr>
                            <th valign="middle" align="center" style="min-width: 16px;" class="pure-checkbox">
                                <input type="checkbox" id="select_all" name="select_all"/>
                                <label></label>
                            </th>
                            <th><?php echo lang("reference"); ?></th>
                            <th><?php echo lang("title"); ?></th>
                            <th><?php echo lang("description"); ?></th>
							<th><?php echo lang("issued_on"); ?></th>
							<th><?php echo lang("valid_till"); ?></th>
							<th><?php echo lang("customer_bill_to"); ?></th>
							<th><?php echo lang("status"); ?></th>
                            <th style="text-align: end;"><?php echo lang("total"); ?></th>
                            <th style="text-align: end;"><?php echo lang("taxes"); ?></th>
                            <th style="text-align: end;"><?php echo lang("discounts"); ?></th>
                            <th style="text-align: end;"><?php echo lang("shipping"); ?></th>
                            <th width="110" style="min-width:110px; text-align: end;"><?php echo lang("actions"); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="15" class="dataTables_empty"><?php echo lang('loading_data'); ?></td>
						</tr>
					</tbody>
				</table>
<?php if (!isset($disable_headings) || !$disable_headings): ?>
            </div>
        </div>
    </div>
<?php endif ?>
<script type="text/javascript">
var estimates_table;
$(document).ready(function() {
    var filter, value;
    <?php
    if( isset($_GET["f"]) && isset($_GET["fv"])){
        echo "filter = '".$_GET['f']."';";
        echo "value = '".$_GET['fv']."';";
    }
    ?>
    estimates_table = $('#estimates_table').dataTable( {
        "oColVis": {
            "aiExclude": [0, 1, 4, 12],
        },
        "aoColumnDefs": [{
            "bVisible": false,
            "aTargets": [2,3,5,9,10,11] ,
        }],
        "aaSorting": [[ 1, "desc" ]],
        'bProcessing'    : true,
        'bServerSide'    : true,
        "bSortCellsTop"  : true,
        'sAjaxSource'    : SITE_URL+'/estimates/getdata',
        'fnServerData': function(sSource, aoData, fnCallback)
        {
            aoData.push( { "name": CSRF_NAME, "value": CSRF_HASH } );
            if( filter != undefined && value != undefined ){
                aoData.push( { "name": "f", "value": filter } );
                aoData.push( { "name": "v", "value": value } );
            }
            <?php
            if( isset($biller_id)){
                echo 'aoData.push( { "name": "biller_id", "value": "'.$biller_id.'" } );';
            }
            ?>
            $.ajax
            ({
                'dataType': 'json',
                'type'    : 'POST',
                'url'     : sSource,
                'data'    : aoData,
                'success' : fnCallback
            });
        },
        'fnDrawCallback': function(){
            $.each($(this).find('a.biller_popover'), function(i, biller_popover){
                $(biller_popover).popover({
                    placement: 'bottom',
                    template: '<div class="popover"><div class="arrow"></div><div class="popover-content"></div></div>',
                    content:'<ul>'+
                    '<li class="dropdown-item"><a href="'+SITE_URL+'/billers/view?id='+$(biller_popover).data("bill_to_id")+'" class="text-inherit sis_modal" sis-modal="estimates_table"><i class="fa fa-expand"></i>'+globalLang["details"]+'</a></li>'+
                    '<li class="dropdown-item">'+filter_format('<i class="fa fa-filter"></i>'+globalLang["filter"], "fullname", $(biller_popover).data("value"), $(biller_popover).data("value"))+'</li>'+
                    '</ul>',
                    html:true,
                });
            });
            $.each($(this).find('a.status_popover'), function(i, status_popover){
                $(status_popover).popover({
                    placement: 'bottom',
                    template: '<div class="popover"><div class="arrow"></div><div class="popover-content"></div></div>',
                    content:'<ul>'+
                    '<li class="dropdown-item"><a href="'+SITE_URL+'/estimates/set_status?id='+$(status_popover).data("id")+'" class="text-inherit sis_modal" sis-modal="estimates_table"><i class="fa fa-pencil"></i>'+globalLang["edit"]+'</a></li>'+
                    '<li class="dropdown-item">'+filter_format('<i class="fa fa-filter"></i>'+globalLang["filter"], "status", $(status_popover).data("status"), $(status_popover).data("value"))+'</li>'+
                    '</ul>',
                    html:true,
                }).click(function(){return false;});
            });
            $(this).find('thead input[name="select_all"]').get(0).checked = false;
            $(this).find('thead input[name="select_all"]').get(0).indeterminate = false;
        },
        "aoColumns": [
            { "sName": 'id'       , "mDataProp": 'id'       , "mRender": checkboxFormat, "bSortable": false, "bSearchable": false},
            { "sName": 'reference', "mDataProp": 'reference', "mRender": reference_format},
            { "sName": 'title'    , "mDataProp": 'title'    , "mRender": title_format},
            { "sName": 'description' , "mDataProp": 'description' , "mRender": desc_format},
	        { "sName": 'date'     , "mDataProp": 'date'     , "mRender": date_format},
	        { "sName": 'date_due' , "mDataProp": 'date_due' , "mRender": date_due_format},
	        { "sName": 'fullname' , "mDataProp": 'fullname' , "mRender": biller_format},
	        { "sName": 'status'   , "mDataProp": 'status'   , "mRender": status_format},
            { "sName": 'total'    , "mDataProp": 'total'    , "mRender": Format_Currency},
            { "sName": 'total_tax', "mDataProp": 'total_tax', "mRender": Format_Currency},
            { "sName": 'total_discount' , "mDataProp": 'total_discount' , "mRender": Format_Currency},
            { "sName": 'shipping' , "mDataProp": 'shipping' , "mRender": Format_Currency},
	        { "bSortable": false, "mRender": actions_format, "bSearchable": false }
        ]
    });
<?php if (!isset($disable_headings) || !$disable_headings): ?>
    estimates_table.advancedSearch({
        aoColumns:[
            null,
            { type: "text", bRegex:true },
            { type: "text", bRegex:true },
            { type: "text", bRegex:true },
            { type: "date-range"},
            { type: "date-range"},
            { type: "text", bRegex:true },
            { type: "select", values: <?php echo json_encode($status_list) ?> },
            { type: "number" },
            { type: "number" },
            { type: "number" },
            { type: "number" },
            null
        ]
    });
<?php endif ?>

    $('.columns-list').on("click", "ul", function(e){
        e.stopPropagation();
    });

    estimates_table.on("click", "a[href='#filter']", function(e){
        estimates_table.prev(".datatable_filter").remove();
        if( filter == $(this).data("filter") && value == $(this).data("value") ){
            filter = undefined;
            value = undefined;
            estimates_table._fnReDraw();
        }else{
            filter = $(this).data("filter");
            value = $(this).data("value");
            column = estimates_table.find('thead th:eq('+$(this).parents("td").index()+')').text();
            text = $(this).data("text");
            estimates_table._fnReDraw();
            $( "<div class='alert alert-info alert-sm datatable_filter'>"+
                '<button type="button" class="btn btn-sm btn-secondary pull-right flip" data-dismiss="alert" aria-hidden="true"><i class="fa fa-close"></i> '+globalLang["clear_filter"]+'</button>'+
                "<p><span>" +
                "<strong>"+ globalLang["filter"] + "</strong> " +
                column + " = " + text +
            "</span></p></div>" ).insertBefore(estimates_table);
        }

        e.preventDefault();
        return false;
    });

    $(document).on('closed', '.datatable_filter', function(){
        filter = undefined;
        value = undefined;
        estimates_table._fnReDraw();
    });

    $('a[href="#refresh-list"]').bind('click', function() {
        estimates_table._fnReDraw();
        return false;
    });

    estimates_table.on("select-count", function(e){
        var table = $(this);
        var select_count = table.data("select-count");
        if( select_count > 0 ){
            html = globalLang["selected"]+" <b>"+select_count+"</b> "+globalLang["estimate"+(select_count==1?"":"s")]+".";
            totals = [];
            var oSettings = estimates_table.fnSettings();
            for (var i = 0; i < oSettings.aoData.length; i++) {
                if( $(oSettings.aoData[i].nTr).is(".row_selected") ){
                    row = oSettings.aoData[i]._aData;
                    if( totals[row.currency] != undefined ){
                        totals[row.currency] += parseFloat(row.total);
                    }else{
                        totals[row.currency] = parseFloat(row.total);
                    }
                }
            }
            tots = [];
            for (var currency in totals) {
                tots.push("<b dir='ltr'>("+Format_Currency(totals[currency], true, {"currency":currency})+")</b>");
            }
            html += globalLang["total"]+" <b>"+tots.join(", ")+"</b>";
            table.closest(".dataTables_wrapper").find('.select_area').html(html).show();
        }else{
            table.closest(".dataTables_wrapper").find('.select_area').hide();
        }
    });

    $(document).on('click', '.delete_selected', function() {
        if( !$(this).is('.disabled') ){
            var selected_rows = {};
            $.each($(".checkable_datatable tr.row_selected .row_checkbox"), function(i, checkbox){
                selected_rows["id["+i+"]"] = $(checkbox).data("id");
            });
            bconfirm(globalLang['alert_confirmation'], function(){
                $('#estimates_table').load_ajax(SITE_URL+"/estimates/delete", 'POST', selected_rows);
            });
        }
        return false;
    });

    $(document).on('click', '.print_selected', function() {
        if( !$(this).is('.disabled') ){
            var selected_rows = [];
            $.each($(".checkable_datatable tr.row_selected .row_checkbox"), function(i, checkbox){
                selected_rows[i] = $(checkbox).data("id");
            });
            MyWindow=window.open(SITE_URL+"/estimates/view?id="+selected_rows.join(","), WINDDOW_NAME, WINDDOW_CONFIGURATION);
        }
        return false;
    });

    $(document).on('click', '.pdf_selected', function() {
        if( !$(this).is('.disabled') ){
            var selected_rows = [];
            $.each($(".checkable_datatable tr.row_selected .row_checkbox"), function(i, checkbox){
                selected_rows[i] = $(checkbox).data("id");
            });
            location.href = SITE_URL+"/estimates/pdf?id="+selected_rows.join(",");
        }
        return false;
    });

    function actions_format(data, type, row, meta){
        actions = '<div class="btn-group">'+
                    '<a href="'+SITE_URL+"/estimates/open/"+row.id+'" class="btn btn-sm btn-secondary m-a-0" >'+
                        '<span>'+globalLang['details']+'</span>'+
                    '</a>'+
                    '<button data-toggle="dropdown" class="dropdown-toggle btn btn-sm btn-secondary" aria-expanded="true">'+
                        '<span><i class="icon-settings"></i></span>'+
                    '</button>'+
                    '<ul class="dropdown-menu dropdown-menu-right">';
        actions += $('#invoices_table').create_datatable_action("eye", SITE_URL+"/estimates/open/"+row.id, globalLang['details'], false);
        <?php if (!$this->ion_auth->in_group(array("customer", "supplier"))): ?>
        actions += $('#estimates_table').create_datatable_action("trash", SITE_URL+"/estimates/delete?id="+row.id, globalLang['delete'], false, true, false, true);
        actions += $('#estimates_table').create_datatable_action("pencil", SITE_URL+"/estimates/edit?id="+row.id, globalLang['edit'], false);
        actions += $('#estimates_table').create_datatable_action("copy", SITE_URL+"/estimates/duplicate?id="+row.id, globalLang['duplicate'], false, true, false, true);
        actions += '<li class="dropdown-divider" ></li>';
        <?php endif ?>

        actions += $('#estimates_table').create_datatable_action("print", SITE_URL+"/estimates/view?id="+row.id, globalLang['print'], true);
        actions += $('#estimates_table').create_datatable_action("download", SITE_URL+"/estimates/pdf?id="+row.id, globalLang['tabletool_pdf'], false);
        <?php if (!$this->ion_auth->in_group(array("customer", "supplier"))): ?>

        actions += $('#estimates_table').create_datatable_action("envelope", SITE_URL+"/estimates/email?id="+row.id, globalLang['email'], false, false, true);

        actions += '<li class="dropdown-divider" ></li>';
        actions += $('#estimates_table').create_datatable_action("clipboard", SITE_URL+"/invoices/create?estimate_id="+row.id, globalLang['convert_to_invoice'], false, true, false, false);
        <?php endif ?>

        <?php if ($this->ion_auth->is_admin()): ?>
        actions += $('#estimates_table').create_datatable_action("history", SITE_URL+"/estimates/activities?id="+row.id, globalLang['activities'], false, false, true);
        <?php endif ?>


        <?php if ($this->ion_auth->in_group(array("customer", "supplier")) ): ?>
        if( row.status == "sent" ){
            actions += '<li class="dropdown-divider" ></li>';
            actions += $('#estimates_table').create_datatable_action("check", SITE_URL+"/estimates/approve/"+row.id, globalLang['approve'], false, true, false, true);
            actions += $('#estimates_table').create_datatable_action("remove", SITE_URL+"/estimates/reject/"+row.id, globalLang['reject'], false, true, false, true);
        }
        <?php endif ?>
        actions += '</ul></div>';
        return "<center>"+actions+"</center>";
    }

    function status_format(x, y ,row) {
        var status = x;
        if( x == "sent" ){
            status = "<span class='label label-tall label-warning'>"+globalLang[x]+"</span>";
        }
        if( x == "accepted" ){
            status = "<span class='label label-tall label-success'>"+globalLang[x]+"</span>";
        }
        if( x == "invoiced" ){
            status = "<span class='label label-tall label-primary'>"+globalLang[x]+"</span>";
        }
        if( x == "canceled" ){
            status = "<span class='label label-tall label-default'>"+globalLang[x]+"</span>";
        }
        if( x == "draft" ){
            status = "<span class='label label-tall label-secondary'>"+globalLang[x]+"</span>";
        }

        <?php if (!$this->ion_auth->in_group(array("customer", "supplier"))): ?>
            var html = "<a href='#' class='text-inherit status_popover' data-toggle='popover' data-id='"+row.id+"' data-status='"+x+"' data-value='"+globalLang[x]+"' >"+status+"</a>";
            return html;
        <?php else: ?>
            return filter_format(status, "status", x, globalLang[x]);
        <?php endif ?>
    }

    function reference_format(value, y ,row) {
        var html = '<a href="'+SITE_URL+'/estimates/open/'+row.id+'"><small class="font-weight-bold">'+value+'</small></a>';
        return  html;
    }

    function biller_format(value, y ,row) {
        <?php if (!$this->ion_auth->in_group(array("customer", "supplier"))): ?>
            var html = "<a class='text-inherit biller_popover' data-toggle='popover' data-bill_to_id='"+row.bill_to_id+"' data-value='"+value+"' ><small>"+value+"</small></a>";
            return html;
        <?php else: ?>
            return filter_format("<small>"+value+"</small>", "bill_to_id", row.bill_to_id, value);
        <?php endif ?>
    }

    function title_format(value) {
        var html = "<small>"+value+"</small>";
        return  filter_format(html, "title", value, value);
    }

    function desc_format(value) {
        var html = "<small class='text-truncate'>"+value+"</small>";
        return  filter_format(html, "description", value, value);
    }

    function date_due_format(value) {
        var html = "<small>"+Format_Date(value)+"</small>";
        return  filter_format(html, "date_due", value, Format_Date(value));
    }

    function date_format(value) {
        var html = "<small>"+Format_Date(value)+"</small>";
        return  filter_format(html, "date", value, Format_Date(value));
    }
});
</script>

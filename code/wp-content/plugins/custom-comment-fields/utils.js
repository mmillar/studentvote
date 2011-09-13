function iti_ccf_fix_zebra_stripes(){
    iti_row_count = 1;
    jQuery('#iti-ccf-fields table tbody tr').each(function(){
        if(iti_row_count%2){
            jQuery(this).removeClass('alternate');
        }else{
            jQuery(this).addClass('alternate');
        }
        iti_row_count++;
    });
}

jQuery(document).ready(function(){

    // remove field
    jQuery('a.iti-ccf-remove').click(function(){
        if(jQuery('#iti-ccf-fields table tbody tr').length>1){
            jQuery(this).parent().parent().remove();
            iti_ccf_fix_zebra_stripes();
        }else{
            jQuery(this).parent().parent().find('input').val('');
        }
        return false;
    });

    // add field
    jQuery('#iti-ccf-fields .add a').click(function(){
        jQuery('#iti-ccf-fields table tbody tr:eq(0)').clone(true).find('input').each(function(){
            jQuery(this).val('');
        }).end().appendTo('#iti-ccf-fields tbody');
        iti_ccf_fix_zebra_stripes();
        return false;
    });

    // sortable
    jQuery('#iti-ccf-fields table tbody').sortable();

});
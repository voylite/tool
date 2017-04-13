jQuery(document).ready(function() {
    jQuery("#checkAll").click(function() {
        jQuery("input[name*='checklist[]']").not(this).prop('checked', this.checked);
    });
    jQuery("#addmerge").click(function() {
        jQuery("#checkAll").css("display","none");
        var counter = jQuery("#addcounter").val();
        jQuery("input[name*='checklist[]']").each(function() {
            if (jQuery(this).is(":checked")) {
                if (counter == jQuery("#addcounter").val()) {
                    jQuery("#addcounter").val(parseInt(counter, 10) + 1);
                }
                jQuery('<input type="checkbox" name="mergelist' + counter + '[]" value="' + jQuery(this).val() + '">').insertAfter(this);
            }else{
                jQuery(this).css("display","none");
            }
        });
        jQuery('<div class="form-field">Enter Separator for headers : <input type="text" name="headerseperator[]" value=","></div><div class="form-field">Enter Separator for body : <input type="text" name="bodyseperator[]" value="|"></div>').insertBefore("#submitwrap");
    });
    jQuery("#genupdate").click(function(){
        
        jQuery("#genupdate").attr("disabled",true);
        jQuery("#genupdate").css("opacity","0.5");
        jQuery("#genupdate").css("cursor","not-allowed");
        jQuery("#newpro").css("display","none");

        var arr = ["Voylite Serial Number ( VSN )","Category"];
        var indexarr = ["sku","categories"];
        var arr2 = ["Height","Height - Measuring Unit"];
        var indexarr2 = ["height"," "];
        var arr3 = ["Width","Width - Measuring Unit"];
        var indexarr3 = ["width"," "];
        var arr4 = ["Diameter","Diameter - Measuring Unit"];
        var indexarr4 = ["diameter"," "];
        var alt = ["alt_text"];
        
        jQuery("input[name^='changelist']").each(function(i,em){
            if(jQuery.inArray(em.step,arr) > -1){
                em.value = indexarr[arr.indexOf(em.step)];
            }
            if(jQuery.inArray(em.step,arr2) > -1){
                em.value = indexarr2[arr2.indexOf(em.step)];
            }
            if(jQuery.inArray(em.step,arr3) > -1){
                em.value = indexarr3[arr3.indexOf(em.step)];
            }
            if(jQuery.inArray(em.step,arr4) > -1){
                em.value = indexarr4[arr4.indexOf(em.step)];
            }
        });

        jQuery("input[type='checkbox']").each(function(i,em){
            if(jQuery.inArray(em.value,arr) > -1){
                jQuery(em).prop("checked",true);
            }
            if(jQuery.inArray(em.value,arr2) > -1){
                jQuery(em).prop("checked",true);
            }
            if(jQuery.inArray(em.value,arr3) > -1){
                jQuery(em).prop("checked",true);
            }
            if(jQuery.inArray(em.value,arr4) > -1){
                jQuery(em).prop("checked",true);
            }
            if(jQuery.inArray(em.value,alt) > -1){
                jQuery(em).prop("checked",true);
            }
        });

        jQuery("#addmerge").trigger("click");
        jQuery("input[name='mergelist1[]']").each(function(i,em){
            if(jQuery.inArray(em.value,arr2) > -1){
                jQuery(em).prop("checked",true);
            }
        });

        jQuery("#addmerge").trigger("click");
        jQuery("input[name='mergelist2[]']").each(function(i,em){
            if(jQuery.inArray(em.value,arr3) > -1){
                jQuery(em).prop("checked",true);
            }
        });

        jQuery("#addmerge").trigger("click");
        jQuery("input[name='mergelist3[]']").each(function(i,em){
            if(jQuery.inArray(em.value,arr4) > -1){
                jQuery(em).prop("checked",true);
            }
        });

        jQuery("input[name='headerseperator[]']").each(function(i,em){
            switch(i){
                case 0:
                case 1:
                case 2:
                    em.value = ' ';
                    break;
            }
        });
        jQuery("input[name='bodyseperator[]']").each(function(i,em){
            switch(i){
                case 0:
                case 1:
                case 2:
                    em.value = ',';
                    break;
            }
        });
    });
    jQuery("#newpro").click(function(){
        
        jQuery("#newpro").attr("disabled",true);
        jQuery("#newpro").css("opacity","0.5");
        jQuery("#newpro").css("cursor","not-allowed");
        jQuery("#genupdate").css("display","none");
        
        var arr = ["Color","Light Direction","No. of bulbs","Style","Product Knowledge & Care Instruction","Height","Height - Measuring Unit","Width","Width - Measuring Unit"];
        var indexarr = ["color","light_direction","number_of_bulbs","style","instructions","height"," ","width"," "];
        var arr2 = ["Weight","Weight - Measuring Unit"];
        var indexarr2 = ["weight"," "];
        var arr3 = ["IMG 1.jpg","IMG 2.jpg","IMG 3.jpg","IMG 4.jpg","IMG 5.jpg","IMG 6.jpg","IMG 7.jpg"];
        var indexarr3 = ["image"," "," "," "," "," "," "];
        var arr4 = ["Category","Product Name","Description","Voylite Serial Number ( VSN )","MRP","tax_class_id","is_in_stock","Stock Quantity"];
        var indexarr4 = ["category","name","description","sku","price","tax_class_id","is_in_stock","stock"];
        jQuery("input[name^='changelist']").each(function(i,em){
            if(jQuery.inArray(em.step,arr) > -1){
                em.value = indexarr[arr.indexOf(em.step)];
            }
            if(jQuery.inArray(em.step,arr2) > -1){
                em.value = indexarr2[arr2.indexOf(em.step)];
            }
            if(jQuery.inArray(em.step,arr3) > -1){
                em.value = indexarr3[arr3.indexOf(em.step)];
            }
            if(jQuery.inArray(em.step,arr4) > -1){
                em.value = indexarr4[arr4.indexOf(em.step)];
            }
        });
        jQuery("input[type='checkbox']").each(function(i,em){
            if(jQuery.inArray(em.value,arr) > -1){
                jQuery(em).prop("checked",true);
            }
            if(jQuery.inArray(em.value,arr2) > -1){
                jQuery(em).prop("checked",true);
            }
            if(jQuery.inArray(em.value,arr3) > -1){
                jQuery(em).prop("checked",true);
            }
            if(jQuery.inArray(em.value,arr4) > -1){
                jQuery(em).prop("checked",true);
            }
        });
        jQuery("#addmerge").trigger("click");
        jQuery("input[name='mergelist1[]']").each(function(i,em){
            if(jQuery.inArray(em.value,arr) > -1){
                jQuery(em).prop("checked",true);
            }
        });
        jQuery("#addmerge").trigger("click");
        jQuery("input[name='mergelist2[]']").each(function(i,em){
            if(jQuery.inArray(em.value,arr2) > -1){
                jQuery(em).prop("checked",true);
            }
        });
        jQuery("#addmerge").trigger("click");
        jQuery("input[name='mergelist3[]']").each(function(i,em){
            if(jQuery.inArray(em.value,arr3) > -1){
                jQuery(em).prop("checked",true);
            }
        });
        jQuery("input[name='headerseperator[]']").each(function(i,em){
            switch(i){
                case 0:
                case 2:
                    em.value = ',';
                    break;
                case 1:
                    em.value = ' ';
                    break;
            }
        });
        jQuery("input[name='bodyseperator[]']").each(function(i,em){
            switch(i){
                case 0:
                case 2:
                    em.value = ',';
                    break;
                case 1:
                    em.value = ' ';
                    break;
            }
        });
    });
});

function fileUpload(form, action_url, div_id) {
    // Create the iframe...
    var iframe = document.createElement("iframe");
    iframe.setAttribute("id", "upload_iframe");
    iframe.setAttribute("name", "upload_iframe");
    iframe.setAttribute("width", "0");
    iframe.setAttribute("height", "0");
    iframe.setAttribute("border", "0");
    iframe.setAttribute("style", "width: 0; height: 0; border: none;");

    // Add to document...
    form.parentNode.appendChild(iframe);
    window.frames['upload_iframe'].name = "upload_iframe";

    iframeId = document.getElementById("upload_iframe");

    // Add event...
    var eventHandler = function() {

        if (iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
        else iframeId.removeEventListener("load", eventHandler, false);

        // Message from server...
        if (iframeId.contentDocument) {
            content = iframeId.contentDocument.body.innerHTML;
        } else if (iframeId.contentWindow) {
            content = iframeId.contentWindow.document.body.innerHTML;
        } else if (iframeId.document) {
            content = iframeId.document.body.innerHTML;
        }

        document.getElementById(div_id).innerHTML = content;

        // Del the iframe...
        jQuery("#upload > div:not(.warning)").remove();
        //setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
    }

    if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
    if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);

    // Set properties of form...
    form.setAttribute("target", "upload_iframe");
    form.setAttribute("action", action_url);
    form.setAttribute("method", "post");
    form.setAttribute("enctype", "multipart/form-data");
    form.setAttribute("encoding", "multipart/form-data");

    // Submit the form...
    form.submit();

    document.getElementById(div_id).innerHTML = "Uploading...";
}
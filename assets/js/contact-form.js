Number.prototype.toMonth =  function () {
    var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    return months[this];
};


jQuery.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    jQuery.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

jQuery.fn.simplyButton = function () {
        if (this['button'])return;
        var el = this;
        el['button'] = function(opt){
            switch(opt){
                case "loading":
                    jQuery(el).addClass('loading');
                    break;
                case "reset":
                    jQuery(el).removeClass('loading');
                    break;
                default:
                    
            }
            return el;
        };
        return el;
}

jQuery.fn.simplyContactFormProcessor = function () {
  
        function getPathURL(url) {
          return (window.location.protocol + "//" + window.location.host + "/wp-content/plugins/simply-contact/assets/php/" + url);
      }
  
      function validateEmail(email) {
          var re = /\S+@\S+\.\S+/;
          return re.test(email);
      }
  
      function validateField(type, value) {
          switch (type) {
          case "name":
  
              var re = /^[a-zA-Z]+$/;
              return re.test(value);
              break;
          case "email":
  
              var re = /\S+@\S+\.\S+/;
              return re.test(value);
              break;
          case "phone":
  
              var re = /^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/;
              return re.test(value);
              break;
  
          default:
              return true;
              break;
          }
      }
  
      function check_data_errors(data) {
          var formData = {};
          formData["error"] = false;
          jQuery.each(jQuery("input, textarea, select", data), function (i, o) {
              var name = jQuery(o).attr("name"),
                  required = jQuery(o).attr("required"),
                  type = jQuery(o).data("type"),
                  value = (type !== undefined && type == "option")? jQuery(o).attr('checked'): jQuery(o).val();
  
              if (required !== undefined && (value == undefined || value == ""))
                  formData["error"] = name + " is required and can not be empty.";
              if (type != undefined && value != undefined && !validateField(type, value))
                  formData["error"] = name + " invalid. Please re-enter.";
              if (name !== undefined) 
                  formData[name] = value || "";
              console.log("Child name: " + name);
          });
          return formData;
      }
  
      function check_response_errors(error) {
          var request_error_mesages = {
              'ok': false,
              'error_0': "E-mail address is empty or invalid. You must provide a valid e-mail address.",
              'error_1': "Invalid destination e-mail.",
              'error_2': "Subject is not defined properly.",
              'error_3': "No message attached. Or message is invalid.",
              'error_5': "Wrong page header sent. Please reload the page and try again.",
              'error_6': "Page timeout. Please reload the page and try again."
          };
          return error.length != 0 ? request_error_mesages[error] : ""
      }
    
    function array_to_text(arr, nl) {
      var text = "";
      nl = nl || "\n";
      for(key in arr){
        if(arr.hasOwnProperty(key)){
          text += key;
          text += ": ";
          text += arr[key];
          text += nl;
          }
        }
      return text;
      }
  
  
  return this.each(function() {
    
    var el = this,
        AlertMSG = jQuery("#simply_contact_form_alert", el).hide().removeClass('hide'),
        SuccessMSG = jQuery("#simply_contact_form_success", el).hide().removeClass('hide'),
        SubmitButton = jQuery("button", el).simplyButton(),
        URL = jQuery("#simply_contact_form_URL", el).val();
    
      jQuery(el).on('submit', function (data) {
          data.preventDefault();
          var formData = check_data_errors(el);
          
          if (!formData["error"]) {
              delete formData["error"];
              SuccessMSG.hide(), AlertMSG.hide();
            	SubmitButton.button("loading");
              var date 	=	new Date,
                  nonce 	= jQuery("#simply_contact_form_nonce", el).val(),
                  from 		= jQuery("#simply_contact_form_name", el).val(),
                  subject = jQuery("#simply_contact_form_subject",el).val(),
                  text 		= array_to_text(formData);
  
              request = jQuery.ajax({
                  url: URL,
                  type: "POST",
                  data: {
                      "simply_contact_form_page": "simply-contact/simply-contact.php",
                      "simply_contact_form_nonce": nonce,
                      "simply_contact_form_action": "enquiry_email",
                      "simply_contact_form_from": from,
                      "simply_contact_form_subject": subject,
                      "simply_contact_form_message": text
                  },
                  dataType: "html"
              }).done(function (error_code) {
                	var error = check_response_errors(error_code);
                  if (error) {
                      AlertMSG.html("Request failed: " + error).show();
            					SubmitButton.button("reset");
                  } else {
                      SuccessMSG.html("Your Request has been successfully sent.").show();
                      SubmitButton.button().hide();
                    //	window.location.href = "";
                  }
              }).fail(function (a, error) {
                  AlertMSG.html("Request failed: " + error).show();
          				SubmitButton.button("reset");
              });
          } else {
            AlertMSG.html(formData["error"]).show();
            
          SubmitButton.button("reset");
            }
          return false;
      });
    });
};

jQuery(function () {
  jQuery(".simply_contact_form").simplyContactFormProcessor();
  simplyContactFirstLoad = false;
});

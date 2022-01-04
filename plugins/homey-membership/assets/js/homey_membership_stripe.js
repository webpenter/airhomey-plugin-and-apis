var createCheckoutSession = function(planId, is_homey_membership, stripe_processor_link, currency, postID) {
		var data = new FormData();
		data.append( "is_homey_membership", 1 );
		data.append( "stripe_processor_link",  stripe_processor_link );
		data.append( "planId",  planId  );
		data.append( "currency",  currency  );
		data.append( "postID",  postID  );
		var basePluginUrl = document.getElementById("basePluginUrl").value;
  return fetch(basePluginUrl+"/ajax-endpoint/create-checkout-session.php", {
    method: "POST",
    body: data
  }).then(function(result) {
      if(result.statusText != 'OK'){
          jQuery("#response_statusText").text(result.statusText);
      }
      console.log(result);
    return result.json();
  });
};

// Handle any errors returned from Checkout
var handleResult = function(result) {
  if (result.error) {
    var displayError = document.getElementById("error-message");
    displayError.textContent = result.error.message;
  }
};

function sendAssertion()
{
  var assertionURL = jQuery('#openbadgeassertion').val();
  //console.log("assertionURL =", assertionURL);
  if(typeof assertionURL !== "undefined")
  {
    OpenBadges.issue([assertionURL],
      function(errors, successes) {
        var output;
        if(errors.length > 0)
        {
           output = "Badge Issueing Failed, Sorry";
        }
        if(successes.length > 0)
        {
          output = "Badge Issued Succesfully";
        }
        jQuery(".openbadge_result").html(output);
    });
  }
}

addInitEvent(sendAssertion);

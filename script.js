
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
           output = LANG.plugins.openbadge.issue_fail;
           console.log("LANG =", LANG);
        }
        if(successes.length > 0)
        {
          output = LANG.plugins.openbadge.issue_success;
        }
        jQuery(".openbadge_result").html(output);
    });
  }
}

addInitEvent(sendAssertion);

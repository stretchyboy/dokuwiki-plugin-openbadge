
function sendAssertion()
{
  var assertionURL = jQuery('#openbadgeassertion').val();
  //console.log("assertionURL =", assertionURL);
  if(typeof assertionURL !== "undefined")
  {
    OpenBadges.issue([assertionURL],
      function(errors, successes) {
      console.log(errors);
      console.log(successes);
    });
  }
}

addInitEvent(sendAssertion);

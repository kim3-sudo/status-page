// Search driver
const accordion = document.getElementsByClassName('accordion-collapse'); //li
const serviceparent = document.getElementById("statusparent"); //ul
function evaluatesearch() {
  var searchquery = document.getElementById("accordionsearchbar").value.toLowerCase(); //filter
  console.log("Searching for " + searchquery);
  for (i = 0; i < accordion.length; i++) {
    headelement = accordion[i].getElementsByClassName("servicehead")[0];
    textval = headelement.textContent || headelement.innerText;
    if (textval.toLowerCase().indexOf(searchquery) > -1) {
      // expand
      console.log("Expand " + accordion[i]);
      accordion[i].classList.remove("collapse");
    } else {
      // collapse
      console.log("Collapse " + accordion[i]);
      accordion[i].classList.add("collapse");
    }
  }
}

function addincidentwarn() {
  console.log("Evaluating");
  var text = document.getElementById("addincidentupdatedescription").value.toLowerCase();
  const pattern = /\[[a-z/ ]*\?\]/;
  if (pattern.test(text)) {
    document.getElementById("addincidentplaceholderwarning").classList.add("d-block");
    document.getElementById("addincidentplaceholderwarning").classList.remove("d-none");
  } else {
    document.getElementById("addincidentplaceholderwarning").classList.add("d-none");
    document.getElementById("addincidentplaceholderwarning").classList.remove("d-block");
  }
}

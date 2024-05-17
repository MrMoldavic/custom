window.addEventListener('load', function() {
	$(".hourLoader").removeClass("loader");
	$(".hourLoader").css("display", 'none');
	// La page est complètement chargée, y compris les styles, les images et les sous-documents
	document.querySelectorAll('.appelDiv').forEach(function(div) {
		div.style.visibility = 'visible';
	});
});

$(document).ready(function() {
	// Gestion des clics sur les boutons avec la classe "appelButton"
	$(".appelButton").on("click", function() {
		this.style.opacity = 0;
		$("#loader-" + this.id).addClass("loader");
		$("#loader-" + this.id).css("opacity", 1);
	});

	// Initialisation des accordéons avec la classe "appel-accordion"
	$(".appel-accordion").accordion({
		collapsible: true,
		active: 2,
	});

	// Initialisation des accordéons avec la classe "appel-accordion-opened"
	$(".appel-accordion-opened").accordion({
		collapsible: true,
	});
});


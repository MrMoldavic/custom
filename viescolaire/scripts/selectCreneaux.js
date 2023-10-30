const select = document.getElementById('fk_creneau');
const divButtons = document.getElementById('div-creneau')

if(select.options.length == 1){
	divButtons.style.opacity = 0
	select.options[select.options.length] = new Option ("<span class='badge  badge-status8 badge-status'>Aucun créneau disponible</span>", "0", 0,1)
}

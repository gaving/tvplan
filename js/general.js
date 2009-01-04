function toggleX(id) {
	if (document.getElementById) {
		var nodeObj = document.getElementById(id)
		nodeObj.style.color = (nodeObj.style.color == 'black') ? 'white' : 'black';
	}
}

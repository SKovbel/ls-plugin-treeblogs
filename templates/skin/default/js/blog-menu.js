function reverseMenu(id) {
	var u = $('m' + id);
	var d = $('d' + id);
	if (u.isVisible()) {
		$(u).set('class', 'regular');
		$(d).set('class', 'regular');
	} else {
		$(u).set('class', 'active');
		$(d).set('class', 'active');
	}

}
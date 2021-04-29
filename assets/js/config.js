function _checkXSS(text) {
	let isXss = false;
	let list = [/onabort/gi, /onblur/gi, /onchange/gi, /onclick/gi, /ondblclick/gi, /onerror/gi, /onfocus/gi, /onkeydown/gi, /onkeypress/gi, /onkeyup/gi, /onload/gi, /onmousedown/gi, /onmousemove/gi, /onmouseout/gi, /onmouseover/gi, /onmouseup/gi, /onreset/gi, /onresize/gi, /onselect/gi, /onsubmit/gi, /onunload/gi, /eval/gi, /ascript:/gi, /style=/gi, /width=/gi, /width:/gi, /height=/gi, /height:/gi, /src=/gi];
	for (let i = 0; i < list.length; i++) {
		if (list[i].test(text)) {
			isXss = true;
			break;
		}
	}
	return isXss;
}
document.addEventListener('DOMContentLoaded', function () {
	const comments = document.querySelectorAll('.comment-body .comment-content p');
	comments.forEach(item => {
		if (!/\{!\{(.*)\}!\}/.test(item.innerHTML)) return;
		if (/"/g.test(item.innerHTML) || _checkXSS(item.innerHTML)) {
			item.innerHTML = '该回复疑似异常，已被系统拦截！';
		} else {
            item.innerHTML = item.innerHTML.replace(/\{!\{(.*)\}!\}/, '<img style="max-width: 100%" src="$1"/>')
		}
	});
});

/**
 * Class GWidget
 *
 * Modeled after QObject https://doc.qt.io/qt-5/qwidget.html
 */
class GWidget extends GObject {

	constructor(parent, tagName = 'div') {

		super(parent);

		this.m_element = document.createElement(tagName);

		if (parent !== null)
			parent.appendChild(this.m_element);

		this.m_element.classList.add('ggui__gwidget');
	}

	get dataset() {
		return this.m_element.dataset;
	}

	get classList() {
		return this.m_element.classList;
	}

	get style() {
		return this.m_element.style;
	}

	get width() {
		return this.m_element.clientWidth;
	}

	get height() {
		return this.m_element.clientHeight;
	}
}

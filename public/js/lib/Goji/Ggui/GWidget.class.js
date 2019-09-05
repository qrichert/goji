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

	getElement() {
		return this.m_element;
	}

	/**
	 * Append a single child element
	 *
	 * @param {GWidget} child
	 */
	appendChild(child) {
		child.setParent(this);
		this.m_element.appendChild(child.getElement());
	}

	/**
	 * Append multiple children elements
	 *
	 * @param {Array<GWidget>} children
	 */
	appendChildren(children) {

		let docFrag = document.createDocumentFragment();

		for (let child of children) {
			child.setParent(this);
			docFrag.appendChild(child.getElement());
		}

		this.m_element.appendChild(docFrag);
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

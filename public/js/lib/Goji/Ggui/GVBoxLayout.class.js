/**
 * Class GVBoxLayout
 */
class GVBoxLayout extends GLayout {

	constructor(parent) {

		super(parent);

		this.m_element.classList.add('ggui__gvboxlayout');

		this.m_element.style.flexDirection = 'column';
	}
}

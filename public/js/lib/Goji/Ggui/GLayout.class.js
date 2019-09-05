/**
 * Class GLayout
 */
class GLayout extends GWidget {

	constructor(parent) {

		super(parent, 'div');

		this.m_element.classList.add('ggui__glayout');

		this.m_element.style.display = 'flex';
		this.m_element.style.justifyContent = 'flex-start';
		this.m_element.style.alignContent = 'flex-start';
		this.m_element.style.alignItems = 'flex-start';
		this.m_element.style.flexWrap = 'wrap';
	}
}

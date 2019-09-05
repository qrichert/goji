/**
 * Class GHBoxLayout
 */
class GHBoxLayout extends GLayout {

	constructor(parent) {

		super(parent);

		this.m_element.classList.add('ggui__ghboxlayout');

		this.m_element.style.flexDirection = 'row';
	}
}

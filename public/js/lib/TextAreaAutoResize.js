function TextAreaAutoResize(parent) {

	var _this = this;

	this.m_parent = parent;
		this.m_parent.style.resize = 'none';
		this.m_parent.style.overflow = 'hidden'; // Prevent srollbars

		this.m_parent.addEventListener('change', function() { _this.resize(); }, false);
		this.m_parent.addEventListener('cut', function() { _this.resizeDelayed(); }, false);
		this.m_parent.addEventListener('paste', function() { _this.resizeDelayed(); }, false);
		this.m_parent.addEventListener('drop', function() { _this.resizeDelayed(); }, false);
		this.m_parent.addEventListener('keydown', function() { _this.resizeDelayed(); }, false);
		this.m_parent.addEventListener('resizerequest', function() { _this.resizeDelayed(); }, false); // CustomEvent

		this.m_parent.closest('form').addEventListener('reset', function() { _this.resizeDelayed(); }, false);

	this.resize = function () {
		_this.m_parent.style.height = 'auto';
		_this.m_parent.style.height = _this.m_parent.scrollHeight + 'px';
	};

	this.resizeDelayed = function () {
		setTimeout(function() { _this.resize(); }, 0);
	};

	this.resizeDelayed();
}

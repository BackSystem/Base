export default function slideUp(
	element: HTMLElement,
	duration: number = 500
): void {
	element.style.removeProperty('display')

	const display = window.getComputedStyle(element).display

	if (display === 'none') {
		element.style.display = 'block'
	}

	const height = element.offsetHeight

	element.style.overflow = 'hidden'
	element.style.height = '0'
	element.style.paddingTop = '0'
	element.style.paddingBottom = '0'
	element.style.marginTop = '0'
	element.style.marginBottom = '0'
	element.style.opacity = '0'

	void element.offsetHeight

	element.style.transitionProperty = 'height, margin, padding, opacity'
	element.style.transitionDuration = `${duration}ms`
	element.style.height = `${height}px`

	element.style.removeProperty('padding-top')
	element.style.removeProperty('padding-bottom')
	element.style.removeProperty('margin-top')
	element.style.removeProperty('margin-bottom')
	element.style.removeProperty('opacity')

	window.setTimeout(() => {
		element.style.removeProperty('height')
		element.style.removeProperty('overflow')
		element.style.removeProperty('transition-duration')
		element.style.removeProperty('transition-property')

		if (element.style.length === 0) {
			element.removeAttribute('style')
		}
	}, duration)
}

export default function slideDown(element: HTMLElement, duration: number = 500) {
	const style = getComputedStyle(element)

	element.style.setProperty('margin-top', style.marginTop)
	element.style.setProperty('height', style.height)
	element.style.setProperty('margin-bottom', style.marginBottom)

	duration = duration / 1000

	let opacityDuration = duration / 2

	element.style.setProperty('transition', `margin ${duration}s, height ${duration}s, border-width ${duration}s, opacity ${opacityDuration}s`)
	element.style.setProperty('transition-timing-function', 'ease-in-out');

	setTimeout(() => {
		element.style.setProperty('margin-top', '0')
		element.style.setProperty('height', '0')
		element.style.setProperty('margin-bottom', '0')
		element.style.setProperty('border-top-width', '0')
		element.style.setProperty('border-bottom-width', '0')
		element.style.setProperty('opacity', '0')

		setTimeout(() => {
			element.remove()
		}, duration * 1000)
	})
}
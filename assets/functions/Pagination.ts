import { onClick } from './Event'
import { html } from './Dom'

const setPagination = (selector: string) => {
	onClick(selector + ' .page-link', event => {
		event.preventDefault()

		const target = event.target as HTMLLinkElement

		const url = target.href

		document.querySelectorAll(selector + ' .page-item').forEach(pageLink => {
			pageLink.classList.add('disabled')
		});

		(document.activeElement as HTMLElement).blur()

		fetch(url).then(response => response.text()).then(data => {
			const body = html(data)

			document.querySelector(selector).replaceWith(body.querySelector(selector))

			window.history.pushState(null, null, url)
		})
	})
}

export { setPagination }
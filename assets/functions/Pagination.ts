import { onClick } from './Event'
import { html } from './Dom'
import displayToast, { ToastType } from './ToastColor'

const translations = {
    'en': 'An error has occurred',
    'fr': 'Une erreur est survenue',
}

const setPagination = (selector: string) => {
    onClick(selector + ' a.page-link', event => {
        event.preventDefault()

        const target = event.target as HTMLLinkElement

        const url = new URL(target.href)

        if (url.searchParams.has('page') && url.searchParams.get('page') === '1') {
            url.searchParams.delete('page')
        }

        const pageLinks = document.querySelectorAll(selector + ':not(:disabled) .page-item')

        pageLinks.forEach(pageLink => pageLink.classList.add('disabled'));

        (document.activeElement as HTMLElement).blur()

        fetch(url.href).then(response => {
            if (response.redirected) {
                window.location.href = response.url
            }

            return response.text()
        }).then(data => {
            const response = html(data)

            const responsePageLinks = response.querySelectorAll(selector + ':not(:disabled) .page-item')

            responsePageLinks.forEach(pageLink => pageLink.classList.add('disabled'))

            if (!(response instanceof HTMLElement)) {
                throw new Error()
            }

            const responseSelector = response.querySelector(selector)
            const existingSelector = document.querySelector(selector)

            if (!responseSelector || !existingSelector) {
                throw new Error()
            }

            existingSelector.replaceWith(responseSelector)

            setTimeout(() => {
                responsePageLinks.forEach(pageLink => pageLink.classList.remove('disabled'))
            }, 10)

            window.history.pushState(null, null, url)
        }).catch(() => {
            pageLinks.forEach(pageLink => pageLink.classList.remove('disabled'))

            const locale = document.documentElement.lang ?? 'en'
            const message = translations[locale] ?? translations['en']

            displayToast(ToastType.Error, message)
        })
    })
}

export { setPagination }

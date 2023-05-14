import { Tooltip } from 'bootstrap'

export {}

declare global {
    interface HTMLElement {
        setLoading(enable: boolean): void
    }
}

const buttons = new Map()

HTMLElement.prototype.setLoading = function (enable: boolean = true) {
    const tooltip = Tooltip.getInstance(this)

    if (tooltip) {
        tooltip.hide()
    }

    if (!buttons.has(this)) {
        const span = document.createElement('span')
        span.classList.add('visually-hidden')
        span.wrapAll(this.childNodes)

        const spinner = document.createElement('span')
        spinner.ariaHidden = 'true'
        spinner.classList.add('spinner-border')
        spinner.classList.add('spinner-border-sm')

        buttons.set(this, [span, spinner])

        this.appendChild(spinner)
    }

    const items = buttons.get(this)

    const span = items[0]
    const spinner = items[1]

    if (enable) {
        this.disabled = true

        span.classList.add('visually-hidden')
        spinner.classList.remove('visually-hidden')
    } else {
        spinner.classList.add('visually-hidden')
        span.removeAttribute('class')

        this.disabled = false
    }
}

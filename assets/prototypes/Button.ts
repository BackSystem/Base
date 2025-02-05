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
        this.classList.add('position-relative')

        const span = document.createElement('span')
        span.classList.add('visually-hidden')
        span.wrapAll(this.childNodes)

        const spinnerDiv = document.createElement('div')
        spinnerDiv.classList.add('spinner-border', 'spinner-border-sm')
        spinnerDiv.role = 'status'

        buttons.set(this, [span, spinnerDiv])

        this.appendChild(spinnerDiv)
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

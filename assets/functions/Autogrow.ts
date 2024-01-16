export class Autogrow {

    private element: HTMLTextAreaElement

    constructor(element: HTMLTextAreaElement) {
        this.element = element

        this.onFocus = this.onFocus.bind(this)
        this.onFocusOut = this.onFocusOut.bind(this)
        this.autogrow = this.autogrow.bind(this)
        this.onResize = this.debounce(this.onResize.bind(this), 300)

        this.element.addEventListener('focusout', this.onFocusOut)

        this.onFocus()
    }

    debounce(callback: Function, delay: number) {
        let timer: NodeJS.Timeout

        return function () {
            let args = arguments
            let context = this

            clearTimeout(timer)

            timer = setTimeout(() => {
                callback.apply(context, args)
            }, delay)
        }
    }

    onFocus() {
        // console.log('onFocus')

        this.element.style.overflow = 'hidden'
        this.element.style.resize = 'none'
        this.element.style.boxSizing = 'border-box'

        this.autogrow()

        window.addEventListener('resize', this.onResize)

        this.element.addEventListener('input', this.autogrow)
    }

    onFocusOut() {
        // console.log('onFocusOut')

        this.element.value = this.element.value.trim()
        this.autogrow()
    }

    onResize() {
        // console.log('onResize')

        this.autogrow()
    }

    autogrow() {
        // console.log('Grow')

        this.element.style.height = 'auto'
        this.element.style.height = this.element.scrollHeight + 'px'
    }

}
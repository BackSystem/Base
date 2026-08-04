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

    private debounce<TArgs extends unknown[]>(
        callback: (...args: TArgs) => void,
        delay: number
    ): (...args: TArgs) => void {
        let timer: ReturnType<typeof setTimeout>

        return (...args: TArgs): void => {
            clearTimeout(timer)

            timer = setTimeout(() => {
                callback(...args)
            }, delay)
        }
    }

    private onFocus(): void {
        this.element.style.overflow = 'hidden'
        this.element.style.resize = 'none'
        this.element.style.boxSizing = 'border-box'

        this.autogrow()

        window.addEventListener('resize', this.onResize)
        this.element.addEventListener('input', this.autogrow)
    }

    private onFocusOut(): void {
        this.element.value = this.element.value.trim()
        this.autogrow()
    }

    private onResize(): void {
        this.autogrow()
    }

    private autogrow(): void {
        this.element.style.height = 'auto'
        this.element.style.height = `${this.element.scrollHeight}px`
    }
}
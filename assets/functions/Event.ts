const on = (selector: string, type: string, callback: (this: HTMLElement, event: Event) => void) => {
    document.body.addEventListener(type, event => {
        let target = event.target as HTMLElement

        if (target.matches(selector) || (target = target.closest(selector))) {
            return callback.call(target, event)
        }
    })
}

const onClick = (selector: string, callback: (this: HTMLElement, event: PointerEvent) => void) => {
    on(selector, 'click', callback)
}

const onInput = (selector: string, callback: (this: HTMLElement, event: InputEvent) => void) => {
    on(selector, 'input', callback)
}

const onChange = (selector: string, callback: (this: HTMLElement, event: Event) => void) => {
    on(selector, 'change', callback)
}

export { on, onClick, onInput, onChange }

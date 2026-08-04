type EventCallback<E extends Event> = (
    this: HTMLElement,
    event: E
) => void

const on = <K extends keyof HTMLElementEventMap>(
    selector: string,
    type: K,
    callback: EventCallback<HTMLElementEventMap[K]>
): void => {
    document.body.addEventListener(type, event => {
        const source = event.target

        if (!(source instanceof Element)) {
            return
        }

        const matched = source.closest(selector)

        if (!(matched instanceof HTMLElement)) {
            return
        }

        callback.call(matched, event)
    })
}

const onClick = (
    selector: string,
    callback: EventCallback<PointerEvent>
): void => {
    on(selector, 'click', callback)
}

const onInput = (
    selector: string,
    callback: EventCallback<InputEvent>
): void => {
    on(selector, 'input', callback)
}

const onChange = (
    selector: string,
    callback: EventCallback<Event>
): void => {
    on(selector, 'change', callback)
}

export { on, onClick, onInput, onChange }

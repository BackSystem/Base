const html = (string: string): HTMLElement => {
    if (!string.includes('</html>')) {
        return document.createRange().createContextualFragment(string).firstChild as HTMLElement
    }

    return new DOMParser().parseFromString(string, 'text/html').firstElementChild as HTMLElement
}

export { html }

import slideDown from '../functions/slideDown'

document.querySelectorAll<HTMLDivElement>('div.toast.show').forEach(toast => {
    let timeout = setTimeout(() => {
        slideDown(toast)
    }, 5000)

    toast.addEventListener('click', () => {
        clearTimeout(timeout)

        slideDown(toast)
    })
})

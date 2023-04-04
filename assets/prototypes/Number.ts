export {}

declare global {
    interface Number {
        pad(number?: number): string
    }
}

Number.prototype.pad = function (number: number = 2) {
    return (new Array(number).join('0') + this).slice(-number)
}

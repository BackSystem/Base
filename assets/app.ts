import './modules/collections'
import './modules/selects'
import './modules/toasts'
import './modules/tooltips'

import './prototypes/Button'
import './prototypes/Element'
import './prototypes/Form'
import './prototypes/Number'
import './prototypes/String'

globalThis.base = (document.querySelector('meta[name="base"]') as HTMLMetaElement)?.content
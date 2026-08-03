# Bundled preview fonts

The files in this directory are used only inside the self-contained SVG live preview. The compact WOFF2 files contain Latin-1 characters and use neutral internal family names (`IPSViewPreviewFont01` through `IPSViewPreviewFont08`). The additional italic TTF cuts and the original Segment7 TTF remain unmodified and keep their original family metadata.

| Preview selection | Source font | Included cuts | License or usage notice |
|---|---|---|---|
| Roboto | Roboto | Regular, Italic, Bold, Bold Italic | Apache License 2.0 |
| RobotoMono | Roboto Mono | Regular, Italic, Bold, Bold Italic | Apache License 2.0 |
| DancingScript | Dancing Script | Regular, Bold | SIL Open Font License 1.1 |
| IndieFlower | Indie Flower | Regular | SIL Open Font License 1.1 |
| OpenSans | Open Sans | Regular, Italic, Bold, Bold Italic | Apache License 2.0 |
| PTSans | PT Sans | Regular, Italic, Bold, Bold Italic | ParaType Free Font License |
| BebasNeue | Bebas Neue | Regular | SIL Open Font License 1.1 |
| Segment7 | G7 Segment 7 S5 | Regular | Listed as “100% Free” by DaFont and as free for personal and commercial use by FontGet |
| Segment7 fallback | Digital Numbers | Regular | SIL Open Font License 1.1; used only if the original TTF is unavailable |

## Required original IPSView font cuts

The extended offline preview expects these unmodified files in this directory:

- `Roboto-RegularItalic.ttf`
- `Roboto-BoldItalic.ttf`
- `RobotoMono-RegularItalic.ttf`
- `RobotoMono-BoldItalic.ttf`
- `OpenSans-RegularItalic.ttf`
- `OpenSans-BoldItalic.ttf`
- `PTSans-RegularItalic.ttf`
- `PTSans-BoldItalic.ttf`
- `Segment7-Regular.ttf`

## Copyright notices

- Roboto: Copyright 2015 Google Inc.
- Roboto Mono: Copyright 2015 Google Inc. All Rights Reserved.
- Dancing Script: Copyright 2016 The Dancing Script Project Authors, with Reserved Font Name "Dancing Script".
- Indie Flower: Copyright 2010 The Indie Flower Authors.
- Open Sans: Digitized data copyright 2010-2011 Google Corporation.
- PT Sans: Copyright 2009 ParaType Ltd. All Rights Reserved.
- Bebas Neue: Copyright 2019 The Bebas Neue Project Authors.
- G7 Segment 7 S5: Created by GSeven.
- Digital Numbers fallback: Copyright 2018 Stephan Ahlf.

## Segment7 source references

- DaFont: `https://www.dafont.com/g7-segment7-s5.font`
- FontGet: `https://www.fontget.com/font/g7-segment-7-s5/`

Both public catalogue pages identify the font as freely usable; FontGet explicitly includes commercial use. No separate license text was supplied with the font file, so this notice records the public source information and attribution without replacing an author-provided license.

The compact WOFF2 files were converted, subset for the preview character range and renamed internally. The original reserved font names are not used as the internal family names of those modified files. The additional italic TTF cuts and `Segment7-Regular.ttf` are loaded only for the selected preview style. The existing Digital Numbers WOFF2 remains solely as a defensive fallback when the original Segment7 TTF is unavailable.

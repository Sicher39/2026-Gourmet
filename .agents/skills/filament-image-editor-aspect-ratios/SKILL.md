---
name: filament-image-editor-aspect-ratios
description: Configure Filament FileUpload image cropping when one or multiple aspect ratios are needed.
---

# Filament FileUpload image-editor aspect ratios

## When to Use
Use when configuring `FileUpload` image cropping in Filament resources, especially when admins need a choice such as square or 16:9.

## Procedure
1. Decide whether the upload needs one fixed crop ratio or several selectable ratios.
2. For several selectable ratios, configure the image editor and the allowed `imageAspectRatios()` only; let the admin open it from the file preview's edit control.
3. For one fixed ratio, automatic opening for that ratio may be used if the UX requires it.
4. State clearly in help text whether the editor opens automatically or through the preview edit control.

## Pitfalls
- Do not combine `automaticallyOpenImageEditorForAspectRatio()` with multiple allowed aspect ratios. Filament throws `InvalidArgumentException`.
- A crop can remove portions of an original image; it cannot recreate image area already lost from a previously stored square crop.
- Do not assume a reported implementation works without manually verifying the resource form and the editor interaction.

## Verification
1. Load the relevant create and edit forms.
2. Confirm no exception is raised.
3. Open the image editor for an existing and a newly selected image.
4. Confirm all intended ratio choices are available and the saved crop is retained.

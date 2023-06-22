name: Bug report
description: Report something utterly broken
title: "[BUG] Oops"
labels: bug
assignees: ''
body:
  - type: markdown
    attributes:
      value: "Shoot with everything I need to know to reproduce this bug, be creative 😄"
  - type: input
    attributes:
      label: Commander Version
      description: Provide the Commander version.
      placeholder: beta
    validations:
      required: true
  - type: input
    attributes:
      label: 🐘 PHP Version
      description: Provide the PHP version.
      placeholder: 8.2.1
    validations:
      required: true
  - type: textarea
    attributes:
      label: Reproduce
      description: Tell me how to break it, what did you expect and whats actually happening 🤷‍♂️
    validations:
      required: true

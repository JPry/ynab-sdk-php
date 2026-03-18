# Security Policy

## Supported Versions

| Version | Supported |
| ------- | --------- |
| 2.x     | Yes       |
| 1.x     | No        |

## Reporting a Vulnerability

If you discover a security vulnerability in this project, please report it responsibly.

**Do not open a public GitHub issue for security vulnerabilities.**

Instead, please report it via one of the following:

- **GitHub Security Advisories**: Use the [Report a Vulnerability](../../security/advisories/new) button on the Security tab of this repository.
- **Email**: Send details to [security@jpry.com](mailto:security@jpry.com).

### What to Include

Please include as much of the following as possible to help us understand and address the issue quickly:

- A description of the vulnerability and its potential impact
- Steps to reproduce or proof-of-concept code
- Any suggested fixes or mitigations

### Response Timeline

- **Acknowledgement**: Within 48 hours of your report
- **Initial assessment**: Within 7 days
- **Resolution**: Dependent on severity and complexity

We will keep you informed throughout the process and credit you in the fix (unless you prefer to remain anonymous).

## Scope

This library is a PHP client for the [YNAB API](https://api.ynab.com). Security concerns most relevant to this project include:

- Improper handling or exposure of YNAB API tokens
- Vulnerabilities introduced by this library's dependencies
- Input validation issues that could lead to unexpected behavior

---
description: Repository Information Overview
alwaysApply: true
---

# FastCaisse Landing Page Information

## Summary

This repository contains the landing page website for FastCaisse, a POS & CRM solution provider for restaurants and retail businesses in Belgium. The website is built as a static HTML site with multiple language versions (English, French, Dutch) and is deployed to GitHub Pages.

## Structure

- **assets/**: Contains CSS and JavaScript files for the website
  - **css/**: Contains the main CSS file for styling
  - **js/**: Contains JavaScript for interactive elements
- **images/**: Contains all website images and media
  - **clients/**: Client logos and testimonials
  - **flags/**: Country flags for language selection
  - **restro/**: Restaurant-specific images and videos
- **.github/workflows/**: Contains GitHub Actions workflow for deployment
- **HTML files**: Multiple HTML files for different pages and language versions

## Language & Runtime

**Language**: HTML, CSS, JavaScript
**Build System**: None (Static HTML)
**Package Manager**: npm (for development tools only)

## Dependencies

**Development Dependencies**:

- prettier: ^3.6.2 (Code formatting)

## Build & Installation

No build process is required as this is a static HTML website. The site can be served locally using any static file server.

```bash
# Install development dependencies
npm install

# Format code with Prettier
npm run format
```

## Deployment

The website is automatically deployed to GitHub Pages using GitHub Actions workflow when changes are pushed to the master branch.

```bash
# GitHub Actions workflow
# Deploys the entire repository to GitHub Pages
```

## Website Structure

**Main Pages**:

- index-en.html: Main landing page (English)
- fastcaisse-restaurant-pos-en.html: Restaurant POS solution page
- fastcaisse-retail-pos-en.html: Retail POS solution page
- fastcaisse-crm-business-management-en.html: CRM solution page
- general-conditions-fr.html, general-conditions-fr.html, general-conditions-fr.html: Terms and conditions in different languages

**Features**:

- Multi-language support (English, French, Dutch)
- Responsive design
- Client testimonials and showcase
- Product information for different business types

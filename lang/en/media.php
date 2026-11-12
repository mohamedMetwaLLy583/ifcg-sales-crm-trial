<?php
return json_decode(<<<'JSON'
{
  "manage": "Banners & media",
  "help": "Manage the homepage banner: upload an image or video, select its language, set the order and publish. Hiding all banners restores the default homepage hero.",
  "preview": "Preview website",
  "add": "Add a banner",
  "default_title": "Default homepage content",
  "disable": "Hide banner",
  "server_limit": "Server file / request limit",
  "title": "Banner title (optional)",
  "description": "Description (optional)",
  "button": "Button label (optional)",
  "link": "Internal link",
  "file": "Image or video",
  "poster": "Video cover image (optional)",
  "order": "Display order",
  "all_languages": "All languages",
  "upload_help": "JPG / PNG / WebP covers up to 5 MB; media up to 20 MB. Videos: MP4 or WebM. Recommended resolution: 1920×1080 or higher. Uploading a small image does not improve its quality. Leave copy blank to use translated homepage text; custom copy is displayed as written for the selected languages.",
  "local_link": "Use an internal link such as /request-quote or /shop.",
  "hero": "IFCG featured products",
  "video_fallback": "Your browser does not support this video.",
  "view": "Open file",
  "previous": "Previous slide",
  "next": "Next slide",
  "footer_about": "Potato and sweet potato products for foodservice, retail and distribution, with local supply, export and private-label enquiries.",
  "company": "About IFCG",
  "business": "Business solutions",
  "rights": "All rights reserved"
}
JSON, true);

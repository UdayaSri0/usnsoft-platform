<?php

namespace App\Modules\Pages\Enums;

enum BlockType: string
{
    case Hero = 'hero';
    case Slider = 'slider';
    case Cta = 'cta';
    case RichText = 'rich_text';
    case ImageGallery = 'image_gallery';
    case VideoEmbed = 'video_embed';
    case FeatureGrid = 'feature_grid';
    case ProductGrid = 'product_grid';
    case TestimonialList = 'testimonial_list';
    case PartnerLogos = 'partner_logos';
    case Timeline = 'timeline';
    case TeamCards = 'team_cards';
    case FaqList = 'faq_list';
    case StatCounters = 'stat_counters';
    case ContactSection = 'contact_section';
    case FormBlock = 'form_block';
    case FileDownloadBlock = 'file_download_block';
    case BlogTeaser = 'blog_teaser';
    case ServicesBlock = 'services_block';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

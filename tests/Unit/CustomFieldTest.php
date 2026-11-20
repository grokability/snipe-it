<?php

namespace Tests\Unit;

use App\Models\CustomField;
use Tests\TestCase;

/*
 * Test strings for db column names  gathered from
 * http://www.omniglot.com/language/phrases/hovercraft.htm
 */
class CustomFieldTest extends TestCase
{
    public function test_format()
    {
        $customfield = CustomField::factory()->make(['format' => 'IP']);
        $this->assertEquals($customfield->getAttributes()['format'], CustomField::PREDEFINED_FORMATS['IP']); // this seems undocumented...
        $this->assertEquals($customfield->format, 'IP');
    }

    public function test_link_for_value_replaces_placeholder_with_value()
    {
        $customfield = CustomField::factory()->make(['link_template' => 'anydesk:{VALUE}']);

        $this->assertEquals('anydesk:123456789', $customfield->linkForValue('123456789'));
    }

    public function test_link_for_value_strips_whitespace_from_value()
    {
        $customfield = CustomField::factory()->make(['link_template' => 'anydesk:{VALUE}']);

        $this->assertEquals('anydesk:1234567890', $customfield->linkForValue(' 1 234 567 890 '));
    }

    public function test_link_for_value_returns_null_for_whitespace_only_value()
    {
        $customfield = CustomField::factory()->make(['link_template' => 'anydesk:{VALUE}']);

        $this->assertNull($customfield->linkForValue('   '));
    }

    public function test_link_for_value_url_encodes_value_but_keeps_at_sign()
    {
        $customfield = CustomField::factory()->make(['link_template' => 'rustdesk://{VALUE}']);

        $this->assertEquals('rustdesk://config%2Fabc%3Fx%3D1', $customfield->linkForValue('config/abc?x=1'));
        $this->assertEquals('rustdesk://pc@server', $customfield->linkForValue('pc@server'));
    }

    public function test_link_for_value_returns_null_without_template_or_value()
    {
        $this->assertNull(CustomField::factory()->make()->linkForValue('123456789'));
        $this->assertNull(CustomField::factory()->make(['link_template' => 'anydesk:{VALUE}'])->linkForValue(''));
        $this->assertNull(CustomField::factory()->make(['link_template' => 'anydesk:{VALUE}'])->linkForValue(null));
    }

    public function test_link_for_value_returns_null_for_unsupported_element()
    {
        $customfield = CustomField::factory()->make([
            'element' => 'textarea',
            'link_template' => 'anydesk:{VALUE}',
        ]);

        $this->assertNull($customfield->linkForValue('123456789'));
    }

    public function test_link_for_value_returns_null_for_unsafe_template()
    {
        // Validation rejects this on save; guard the render path anyway for
        // values written to the database by other means.
        $customfield = CustomField::factory()->make(['link_template' => 'javascript:alert({VALUE})']);

        $this->assertNull($customfield->linkForValue('1'));
    }

    public function test_link_template_validity()
    {
        $this->assertTrue(CustomField::isValidLinkTemplate('anydesk:{VALUE}'));
        $this->assertTrue(CustomField::isValidLinkTemplate('rustdesk://{VALUE}'));
        $this->assertTrue(CustomField::isValidLinkTemplate('https://intranet.example.com/pc/{VALUE}'));

        $this->assertFalse(CustomField::isValidLinkTemplate('anydesk:'));
        $this->assertFalse(CustomField::isValidLinkTemplate('{VALUE}'));
        $this->assertFalse(CustomField::isValidLinkTemplate(' anydesk:{VALUE}'));
        $this->assertFalse(CustomField::isValidLinkTemplate('JavaScript:alert({VALUE})'));
        $this->assertFalse(CustomField::isValidLinkTemplate("java\tscript:alert({VALUE})"));
        $this->assertFalse(CustomField::isValidLinkTemplate('data:text/html,{VALUE}'));
        $this->assertFalse(CustomField::isValidLinkTemplate('vbscript:{VALUE}'));
    }

    public function test_db_name_ascii()
    {
        $customfield = new CustomField;
        $customfield->name = 'My hovercraft is full of eels';
        $customfield->id = 1337;
        $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_my_hovercraft_is_full_of_eels_1337');
    }

    // Western Europe
    public function test_db_name_latin()
    {
        $customfield = new CustomField;
        $customfield->name = 'My hovercraft is full of eels';
        $customfield->id = 1337;
        $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_my_hovercraft_is_full_of_eels_1337');
    }

    // Asian
    public function test_db_name_chinese()
    {
        $customfield = new CustomField;
        $customfield->name = '我的氣墊船裝滿了鱔魚';
        $customfield->id = 1337;
        if (function_exists('transliterator_transliterate')) {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_wo_de_qi_dian_chuan_zhuang_man_le_shan_yu_1337');
        } else {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_aecsae0ase1eaeaeoees_1337');
        }
    }

    public function test_db_name_japanese()
    {
        $customfield = new CustomField;
        $customfield->name = '私のホバークラフトは鰻でいっぱいです';
        $customfield->id = 1337;
        if (function_exists('transliterator_transliterate')) {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_sinohohakurafutoha_manteihhaitesu_1337');
        } else {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_caafafafaafcafafae0aaaaaaa_1337');
        }
    }

    public function test_db_name_korean()
    {
        $customfield = new CustomField;
        $customfield->name = '내 호버크라프트는 장어로 가득 차 있어요';
        $customfield->id = 1337;
        if (function_exists('transliterator_transliterate')) {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_nae_hobeokeulapeuteuneun_jang_eolo_gadeug_1337');
        } else {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_e_ie2ieiises_izieoe_e0e_i0_iziis_1337');
        }
    }

    // Nordic languages
    public function test_db_name_non_latin_euro()
    {
        $customfield = new CustomField;
        $customfield->name = 'Mój poduszkowiec jest pełen węgorzy';
        $customfield->id = 1337;
        if (function_exists('transliterator_transliterate')) {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_moj_poduszkowiec_jest_pelen_wegorzy_1337');
        } else {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_ma3j_poduszkowiec_jest_peaen_waegorzy_1337');
        }
    }

    //
    public function test_db_name_turkish()
    {
        $customfield = new CustomField;
        $customfield->name = 'Hoverkraftım yılan balığı dolu';
        $customfield->id = 1337;
        if (function_exists('transliterator_transliterate')) {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_hoverkraftim_yilan_baligi_dolu_1337');
        } else {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_hoverkraftaem_yaelan_balaeaeyae_dolu_1337');
        }
    }

    public function test_db_name_arabic()
    {
        $customfield = new CustomField;
        $customfield->name = 'حَوّامتي مُمْتِلئة بِأَنْقَلَيْسون';
        $customfield->id = 1337;
        if (function_exists('transliterator_transliterate')) {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_hwamty_mmtlyt_banqlyswn_1337');
        } else {
            $this->assertEquals($customfield->convertUnicodeDbSlug(), '_snipeit_ouzuuouoaus_uuuuoauuooc_ououzuuuuzuuzusuo_1337');
        }
    }
}

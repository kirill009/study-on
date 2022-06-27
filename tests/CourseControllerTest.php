<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\DataFixtures\LessonFixtures;
use App\Entity\Course;
use Symfony\Component\DomCrawler\Crawler;

class CourseControllerTest extends AbstractTest
{
    private static $data = [
        "name" => "Метрология",
        "description" => "Курс представляет собой изучение основных понятий и определений метрологии, принципов действия аналоговых и цифровых средств измерений, определения метрологических характеристики средств измерений, способы их нормирования и представления, методов и способов измерений электрических и неэлектрических величин, основ стандартизации и сертификации."
    ];

    private static $dataErrors = [
        "name" => "Также как реализация намеченных плановых заданий выявляет срочную потребность форм воздействия. В рамках спецификации современных стандартов, тщательные исследования конкурентов, вне зависимости от их уровня, должны быть рассмотрены исключительно в разрезе маркетинговых и финансовых предпосылок.",
        "description" => "Значимость этих проблем настолько очевидна, что сложившаяся структура организации играет важную роль в формировании дальнейших направлений развития. Приятно, граждане, наблюдать, как диаграммы связей будут объединены в целые кластеры себе подобных. Идейные соображения высшего порядка, а также современная методология разработки не оставляет шанса для поэтапного и последовательного развития общества! Учитывая ключевые сценарии поведения, повышение уровня гражданского сознания обеспечивает широкому кругу (специалистов) участие в формировании глубокомысленных рассуждений. Не следует, однако, забывать, что сложившаяся структура организации, а также свежий взгляд на привычные вещи - безусловно открывает новые горизонты для направлений прогрессивного развития. Являясь всего лишь частью общей картины, действия представителей оппозиции могут быть рассмотрены исключительно в разрезе маркетинговых и финансовых предпосылок. Равным образом, постоянное информационно-пропагандистское обеспечение нашей деятельности предполагает независимые способы реализации направлений прогрессивного развития. Высокий уровень вовлечения представителей целевой аудитории является четким доказательством простого факта: семантический разбор внешних противодействий однозначно определяет каждого участника как способного принимать собственные решения касаемо новых предложений."
    ];

    protected function setUp(): void
    {
        static::getClient();
        $this->loadFixtures($this->getFixtures());
    }

    private function translit(string $text)
    {
        $transliterator = \Transliterator::create('Any-Latin');
        $transliteratorToASCII = \Transliterator::create('Latin-ASCII');

        return $transliteratorToASCII->transliterate($transliterator->transliterate($text));
    }

    protected function getFixtures(): array
    {
        return [
            CourseFixtures::class,
            LessonFixtures::class
        ];
    }

    public function testActionsController(): void
    {
        $this->setUp();
        $client = static::$client;

        $client->request('GET', '/courses/404');
        $this->assertResponseNotFound();
        $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $entityManager = self::getEntityManager();
        $courses = $entityManager->getRepository(Course::class)->findAll();
        
        foreach ($courses as $course) {
            $client->request('GET', '/courses/' . $course->getId());
            $this->assertResponseOk();

            $client->request('GET', '/courses/' . $course->getId() . '/edit');
            $this->assertResponseOk();
    
            $client->request('POST', '/courses/' . $course->getId() . '/edit');
            $this->assertResponseOk();

            $client->request('GET', '/courses/new');
            $this->assertResponseOk();
    
            $client->request('POST', '/courses/new');
            $this->assertResponseOk();
        }
    }

    public function testPageIndex(): void
    {
        $this->setUp();
        $client = static::$client;
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $entityManager = self::getEntityManager();
        $courses = $entityManager->getRepository(Course::class)->findAll();
        $this->assertCount(count($courses), $crawler->filter('a.link'));
    }

    public function testPageNew(): void
    {
        $this->setUp();
        $client = static::$client;
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.create')->link();
        $client->click($link);
        $this->assertResponseOk();

        $client->submitForm('courseAddEdit', [
            'course[code]' => $this->translit(self::$data["name"]),
            'course[name]' => self::$data["name"],
            'course[description]' => self::$data["description"]
        ]);
        $this->assertTrue($client->getResponse()->isRedirect('/courses/'));
        $crawler = $client->followRedirect();

        $entityManager = self::getEntityManager();
        $courses = $entityManager->getRepository(Course::class)->findAll();
        $this->assertCount(count($courses), $crawler->filter('a.link'));

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.create')->link();
        $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('courseAddEdit', [
            'course[code]' => '',
            'course[name]' => '',
            'course[description]' => self::$data["description"]
        ]);
        $errorCode = $crawler->filter('span.form-error-message')->eq(0);
        self::assertEquals('This value should not be blank.', $errorCode->text());
        $errorName = $crawler->filter('span.form-error-message')->eq(1);
        self::assertEquals('This value should not be blank.', $errorName->text());

        $crawler = $client->submitForm('courseAddEdit', [
            'course[code]' => self::$dataErrors["name"],
            'course[name]' => self::$dataErrors["name"],
            'course[description]' => self::$dataErrors["description"]
        ]);
        $errorCode = $crawler->filter('span.form-error-message')->eq(0);
        self::assertEquals('This value is too long. It should have 255 characters or less.', $errorCode->text());
        $errorName = $crawler->filter('span.form-error-message')->eq(1);
        self::assertEquals('This value is too long. It should have 255 characters or less.', $errorName->text());
        $errorDescription = $crawler->filter('span.form-error-message')->eq(2);
        self::assertEquals(
            'This value is too long. It should have 1000 characters or less.',
            $errorDescription->text()
        );
    }
    public function testPageEdit(): void
    {
        $this->setUp();
        $client = static::$client;
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.edit')->link();
        $client->click($link);
        $this->assertResponseOk();

        $client->submitForm('courseAddEdit', [
            'course[code]' => $this->translit(self::$data["name"]),
            'course[name]' => self::$data["name"],
            'course[description]' => self::$data["description"]
        ]);
        $this->assertTrue($client->getResponse()->isRedirect('/courses/'));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $link = $crawler->filter('a.link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.edit')->link();
        $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('courseAddEdit', [
            'course[code]' => self::$dataErrors["name"],
            'course[name]' => self::$dataErrors["name"],
            'course[description]' => self::$dataErrors["description"]
        ]);
        $errorCode = $crawler->filter('span.form-error-message')->eq(0);
        self::assertEquals('This value is too long. It should have 255 characters or less.', $errorCode->text());
        $errorName = $crawler->filter('span.form-error-message')->eq(1);
        self::assertEquals('This value is too long. It should have 255 characters or less.', $errorName->text());
        $errorDescription = $crawler->filter('span.form-error-message')->eq(2);
        self::assertEquals(
            'This value is too long. It should have 1000 characters or less.',
            $errorDescription->text()
        );

        /*$crawler = $client->submitForm('courseAddEdit', [
            'course[code]' => ' ',
            'course[name]' => ' ',
            'course[description]' => self::$data["description"]
        ]);
        $errorCode = $crawler->filter('span.form-error-message')->eq(0);
        self::assertEquals('This value should not be blank.', $errorCode->text());
        $errorName = $crawler->filter('span.form-error-message')->eq(1);
        self::assertEquals('This value should not be blank.', $errorName->text());*/
    }
    public function testPageShow(): void
    {
        $this->setUp();
        $client = static::$client;
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $entityManager = self::getEntityManager();

        $crawler->filter('a.link')->each(function (Crawler $node, $i) use ($client, $entityManager) {
            $crawler = $client->click($node->link());
            $this->assertResponseOk();

            $course = $entityManager->getRepository(Course::class)->findOneBy(['name' => $node->text()]);
            $this->assertCount(count($course->getLessons()), $crawler->filter('a.link'));
        });
    }
    public function testDelete(): void
    {
        $this->setUp();
        $client = static::$client;
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $courseCount = count($crawler->filter('a.link'));
        $link = $crawler->filter('a.link')->first()->link();
        $client->click($link);
        $this->assertResponseOk();

        $client->submitForm('courseDelete');
        $this->assertTrue($client->getResponse()->isRedirect('/courses/'));
        $client->followRedirect();
        $this->assertResponseOk();

        $entityManager = self::getEntityManager();
        $courses = $entityManager->getRepository(Course::class)->findAll();
        --$courseCount;
        $this->assertCount($courseCount, count($courses));
    }
}

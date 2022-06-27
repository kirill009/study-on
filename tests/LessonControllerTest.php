<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\DataFixtures\LessonFixtures;
use App\Entity\Course;
use App\Entity\Lesson;
use Symfony\Component\DomCrawler\Crawler;

class LessonControllerTest extends AbstractTest
{
    private static $data = [
        "name" => "Метрология",
        "content" => "Курс представляет собой изучение основных понятий и определений метрологии, принципов действия аналоговых и цифровых средств измерений, определения метрологических характеристики средств измерений, способы их нормирования и представления, методов и способов измерений электрических и неэлектрических величин, основ стандартизации и сертификации.",
        "number" => 999
    ];

    private static $dataErrors = [
        "name" => "Также как реализация намеченных плановых заданий выявляет срочную потребность форм воздействия. В рамках спецификации современных стандартов, тщательные исследования конкурентов, вне зависимости от их уровня, должны быть рассмотрены исключительно в разрезе маркетинговых и финансовых предпосылок.",
        "content" => "Значимость этих проблем настолько очевидна, что сложившаяся структура организации играет важную роль в формировании дальнейших направлений развития. Приятно, граждане, наблюдать, как диаграммы связей будут объединены в целые кластеры себе подобных. Идейные соображения высшего порядка, а также современная методология разработки не оставляет шанса для поэтапного и последовательного развития общества! Учитывая ключевые сценарии поведения, повышение уровня гражданского сознания обеспечивает широкому кругу (специалистов) участие в формировании глубокомысленных рассуждений. Не следует, однако, забывать, что сложившаяся структура организации, а также свежий взгляд на привычные вещи - безусловно открывает новые горизонты для направлений прогрессивного развития. Являясь всего лишь частью общей картины, действия представителей оппозиции могут быть рассмотрены исключительно в разрезе маркетинговых и финансовых предпосылок. Равным образом, постоянное информационно-пропагандистское обеспечение нашей деятельности предполагает независимые способы реализации направлений прогрессивного развития. Высокий уровень вовлечения представителей целевой аудитории является четким доказательством простого факта: семантический разбор внешних противодействий однозначно определяет каждого участника как способного принимать собственные решения касаемо новых предложений.",
        "number" => 9999
    ];

    protected function setUp(): void
    {
        static::getClient();
        $this->loadFixtures($this->getFixtures());
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

        $client->request('GET', '/lessons/404');
        $this->assertResponseNotFound();

        $entityManager = self::getEntityManager();
        $lessons = $entityManager->getRepository(Lesson::class)->findAll();

        foreach ($lessons as $lesson) {
            $client->request('GET', '/lessons/' . $lesson->getId());
            $this->assertResponseOk();

            $client->request('GET', '/lessons/' . $lesson->getId() . '/edit');
            $this->assertResponseOk();
    
//            $client->request('GET', '/lessons/new', [
//                'query' => [
//                    'course_id' => 1
//                ]
//            ]);
//            print_r($client->getResponse()->getStatusCode());die();
//            //$this->assertResponseOk();
        }
    }

    public function testPageNew(): void
    {
        $this->setUp();
        $client = static::$client;
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('a.link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.createLesson')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->selectButton('lessonAddEdit')->form();
        $entityManager = self::getEntityManager();
        $course = $entityManager->getRepository(Course::class)->findOneBy(['id' => $form['lesson[course]']->getValue()]);

        $client->submitForm('lessonAddEdit', [
            'lesson[name]' => self::$data["name"],
            'lesson[content]' => self::$data["content"],
            'lesson[number]' => self::$data["number"],
            'lesson[course]' => $course->getId()
        ]);
        $this->assertTrue($client->getResponse()->isRedirect('/courses/' . $course->getId()));
        $crawler = $client->followRedirect();

        $lessons = $entityManager->getRepository(Lesson::class)->findBy(['course' => $course->getId()]);
        $this->assertCount(count($lessons), $crawler->filter('a.link'));

        $link = $crawler->filter('a.createLesson')->link();
        $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('lessonAddEdit', [
            'lesson[name]' => '',
            'lesson[content]' => '',
            'lesson[number]' => ''
        ]);
        $errorName = $crawler->filter('span.form-error-message')->eq(0);
        self::assertEquals('This value should not be blank.', $errorName->text());
        $errorContent = $crawler->filter('span.form-error-message')->eq(1);
        self::assertEquals('This value should not be blank.', $errorContent->text());
        $errorNumber = $crawler->filter('span.form-error-message')->eq(2);
        self::assertEquals('This value should not be blank.', $errorNumber->text());

        $crawler = $client->submitForm('lessonAddEdit', [
            'lesson[name]' => self::$dataErrors["name"],
            'lesson[content]' => self::$dataErrors["content"],
            'lesson[number]' => self::$dataErrors["number"]
        ]);
        $errorName = $crawler->filter('span.form-error-message')->eq(0);
        self::assertEquals('This value is too long. It should have 255 characters or less.', $errorName->text());
        $errorNumber = $crawler->filter('span.form-error-message')->eq(1);
        self::assertEquals('This value should be between 1 and 1000.', $errorNumber->text());
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

        $link = $crawler->filter('a.link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->selectButton('lessonAddEdit')->form();
        $entityManager = self::getEntityManager();
        $course = $entityManager->getRepository(Course::class)->findOneBy(['id' => $form['lesson[course]']->getValue()]);

        $client->submitForm('lessonAddEdit', [
            'lesson[name]' => self::$data["name"],
            'lesson[content]' => self::$data["content"],
            'lesson[number]' => self::$data["number"],
            'lesson[course]' => $course->getId()
        ]);
        $this->assertTrue($client->getResponse()->isRedirect('/courses/' . $course->getId()));
        $crawler = $client->followRedirect();

        $lessons = $entityManager->getRepository(Lesson::class)->findBy(['course' => $course->getId()]);
        $this->assertCount(count($lessons), $crawler->filter('a.link'));

        $link = $crawler->filter('a.link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('a.edit')->link();
        $client->click($link);
        $this->assertResponseOk();

        $crawler = $client->submitForm('lessonAddEdit', [
            'lesson[name]' => self::$dataErrors["name"],
            'lesson[content]' => self::$dataErrors["content"],
            'lesson[number]' => self::$dataErrors["number"]
        ]);
        $errorName = $crawler->filter('span.form-error-message')->eq(0);
        self::assertEquals('This value is too long. It should have 255 characters or less.', $errorName->text());
        $errorNumber = $crawler->filter('span.form-error-message')->eq(1);
        self::assertEquals('This value should be between 1 and 1000.', $errorNumber->text());

//        $crawler = $client->submitForm('lessonAddEdit', [
//            'lesson[name]' => '',
//            'lesson[content]' => '',
//            'lesson[number]' => ''
//        ]);
//        $errorName = $crawler->filter('span.form-error-message')->eq(0);
//        self::assertEquals('This value should not be blank.', $errorName->text());
//        $errorContent = $crawler->filter('span.form-error-message')->eq(1);
//        self::assertEquals('This value should not be blank.', $errorContent->text());
//        $errorNumber = $crawler->filter('span.form-error-message')->eq(2);
//        self::assertEquals('This value should not be blank.', $errorNumber->text());
    }
    
    public function testPageShow(): void
    {
        $this->setUp();
        $client = static::$client;
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();
        
        $crawler->filter('a.link')->each(function (Crawler $node, $i) use ($client) {
            $crawler = $client->click($node->link());
            $this->assertResponseOk();
            $crawler->filter('a.link')->each(function (Crawler $node, $i) use ($client) {
                $client->click($node->link());
                $this->assertResponseOk();
            });
        });
    }
    
    public function testDelete(): void
    {
        $this->setUp();
        $client = static::$client;
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();
        
        $link = $crawler->filter('a.link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        
        $lessonsCount = count($crawler->filter('a.link'));
        $link = $crawler->filter('a.link')->first()->link();
        $client->click($link);
        $this->assertResponseOk();
        
        $client->submitForm('lessonDelete');
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertResponseOk();
    }
}

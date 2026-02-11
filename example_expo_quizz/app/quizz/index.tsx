import { useRouter } from "expo-router";
import { Pressable, ScrollView, Text } from "react-native";

const HomePage = () => {
  // Partie logique

  const router = useRouter();

  // ["/"]

  // router.push("/quizz")

  // ["/", "/quizz"]

  // router.push("/quizz/easy")

  // ["/", "/quizz","/quizz/easy"]

  // router.back()

  // ["/", "/quizz"]

  return (
    <ScrollView>
      <Pressable onPress={() => router.push("/quizz/quizz")}>
        <Text>Faire un quizz</Text>
      </Pressable>
    </ScrollView>
  );
};

export default HomePage;

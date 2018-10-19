// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.psi.PsiElement;

public interface LgcArgField extends PsiElement {

  @NotNull
  List<LgcArgDoc> getArgDocList();

  @NotNull
  List<LgcArgField> getArgFieldList();

  @Nullable
  LgcAttributeValue getAttributeValue();

  @NotNull
  LgcEnd getEnd();

  @Nullable
  PsiElement getEquals();

  @Nullable
  PsiElement getIndentDecrease();

  @Nullable
  PsiElement getIndentIncrease();

  @NotNull
  PsiElement getModArg();

}
